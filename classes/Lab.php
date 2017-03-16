<?php
/**
 * Establishes a Lab class that interacts with the LabTest table and LabComponentsAssociation
 * This class groups the LabComponents into coherent lab panels that can be ordered
 *
 * Public Method List
 *  - labTestInDatabase
 *  - labHasLabComponent
 *  - getLabTestId
 *  - getLabAssociationIds
 *  - getLabComponents
 *  - store
 *  - editName
 *  - editCost
 *  - delete
 *  - addComponent
 *  - removeComponent
 * 
 * (Finished and tested)
 */
class Lab
{
    /**
     * @var string $name The name of the lab panel
     */
    public $name;

    /**
     * @var float $cost The cost of the lab panel to be run
     */
    public $cost;

    /**
     * @var LabComponents[] $lab_components Array of lab components in the lab panel
     */
    public $lab_components = array();

    /**
     * @var PDO $con Database connection
     */
    private $con;

    /**
     * Initializes the lab with a name, cost, list of components, and a database connection
     * @param string $name The name of the lab
     * @param float $cost The cost of ordering the lab
     * @param LabComponents[] $lab_components An array of LabComponents objects that lists the components of the current lab
     * @param PDO $con The database connection passed to the object
     * @return void
     */
    public function __construct($name, $cost, PDO $con)
    {
        if (! is_string($name) && ! is_numeric($cost) && ! is_array($lab_components)) {
            throw new Exception("The lab name must be a string; the cost must be a number; and the lab components must be an array of LabComponents objects");
        }

        // Create lab name
        $this->name = $name;

        // load components
        $this->cost = $cost;

        // Database connection
        $this->con = $con;

        // load lab components 
        if ($this->labTestInDatabase()) {
            $this->lab_components = $this->getLabComponents();
        }
    }

    /**
     * Verifies that the lab test exists in the database already
     * @return boolean
     */
    private function labTestInDatabase()
    {
        // Query to see if lab test is in the database already
        $query = "SELECT COUNT(*) FROM `LabTests` WHERE `LabTest` = :LabTest;";
        $stmt_check_duplicate = $this->con->prepare($query);
        $stmt_check_duplicate->bindParam(":LabTest", $this->name);
        $stmt_check_duplicate->execute();
        $lab_test_count = $stmt_check_duplicate->fetch()[0];

        // Lab test already in the database
        if ($lab_test_count > 0) {
            return true;
        }

        return false;
    }

    /**
     * Verifies that the lab association exists in the database already
     * @return boolean
     */
    private function labHasLabComponent(LabComponent $lab_component)
    {
        // Get IDs for the lab test and the components first
        $lab_test_id = $this->getLabTestId();
        $lab_component_id = $lab_component->getId();

        // Query to seeif the lab assocation is already in the database
        $query = "SELECT COUNT(*) FROM `LabComponentsAssociation` WHERE `LabTestId` = :LabTestId AND `LabTestComponentId` = :LabTestComponentId;";
        $stmt_check_duplicate = $this->con->prepare($query);
        $stmt_check_duplicate->bindParam(":LabTestId", $lab_test_id);
        $stmt_check_duplicate->bindParam(":LabTestComponentId", $lab_component_id);
        $stmt_check_duplicate->execute();
        $lab_association_count = $stmt_check_duplicate->fetch()[0];

        // Lab association is already in the database
        if ($lab_association_count > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns the id of a lab test
     * @return int
     */
    public function getLabTestId()
    {
        // Lab test is not in the database and therefore the id can't be retrieved
        if (! $this->labTestInDatabase()) {
            $this->store();
        }

        $query = "SELECT `LabTestId` FROM `LabTests` WHERE `LabTest` = :LabTest;";
        $stmt_lab_id = $this->con->prepare($query);
        $stmt_lab_id->bindParam(":LabTest", $this->name);
        $stmt_lab_id->execute();
        return $stmt_lab_id->fetch()[0];
    }

    /**
     * Returns an array of the lab association IDs for the given lab
     * @return array
     */
    public function getLabAssociationIds()
    {
        if (! $this->labTestInDatabase()) {
            throw new Exception("The lab test is not stored in the database");
        }

        $lab_test_id = $this->getLabTestId();

        $query = "SELECT `LabComponentAssociationId` FROM `LabComponentsAssociation` WHERE `LabTestId` = :LabTestId;";
        $stmt_lab_components_id = $this->con->prepare($query);
        $stmt_lab_components_id->bindParam(":LabTestId", $lab_test_id);
        $stmt_lab_components_id->execute();
        return $stmt_lab_components_id->fetchAll();
    }

    /**
     * Gets the lab components associated with the lab
     * @return LabComponent[]
     */
    public function getLabComponents()
    {
        if (! $this->labTestInDatabase()) {
            throw new Exception("This lab test (" . $this->name . ") is not in the database");
        }

        $lab_test_id = $this->getLabTestId();

        $query = "SELECT `LabTestsComponents`.`LabTestComponent`, `Units`.`Unit`
                    FROM `LabComponentsAssociation`
                    INNER JOIN `LabTestsComponents` USING (`LabTestComponentId`)
                    INNER JOIN `Units` ON `LabTestsComponents`.`LabTestComponentDefaultUnitId` = `Units`.`UnitId`
                    WHERE `LabTestId` = :LabTestId;";
        $stmt_get_lab_components = $this->con->prepare($query);
        $stmt_get_lab_components->bindParam(":LabTestId", $lab_test_id);
        $stmt_get_lab_components->execute();
        
        $lab_component_array = array();
        while ($lab_component_info = $stmt_get_lab_components->fetch()) {
            $lab_component_array[] = new LabComponent($lab_component_info['LabTestComponent'], $lab_component_info['Unit'], $this->con);
        }

        return $lab_component_array;
    }

    /**
     * Creates a lab test from the Lab object by storing the lab name in the LabTest and associating the lab test with the provided lab components
     * @return void
     */
    public function store()
    {
        // Lab test is already in the database and should not be allowed to be created again
        // Editing is the better route if lab components need to be modified
        if ($this->labTestInDatabase()) {
            throw new Exception("This lab test is already in the database");
        }

        // Inserts a new lab test
        $query = "INSERT INTO `LabTests` (`LabTest`, `Cost`) VALUES (:LabTest, :Cost);";
        $stmt_create_lab = $this->con->prepare($query);
        $stmt_create_lab->bindParam(":LabTest", $this->name);
        $stmt_create_lab->bindParam(":Cost", $this->cost);
        $stmt_create_lab->execute();
    }

    /**
     * Edits the name of the lab test
     * @param string $name The name to change the lab test to
     * @return void
     */
    public function editName($name)
    {
        if (! is_string($name)) {
            throw new Exception("The lab name (" . $name . ") must be a string");
        }

        if (! $this->labTestInDatabase()) {
            throw new Exception("The lab test (" . $this->name . ") is NOT in the database");
        }

        $lab_test_id = $this->getLabTestId();

        $query = "UPDATE `LabTests` SET `LabTest` = :LabTest WHERE `LabTestId` = :LabTestId;";
        $stmt_edit_name = $this->con->prepare($query);
        $stmt_edit_name->bindParam(":LabTest", $name);
        $stmt_edit_name->bindParam(":LabTestId", $lab_test_id);
        $stmt_edit_name->execute();

        $this->name = $name;
    }

    /**
     * Edits the cost of the lab test
     * @param number $cost The cost to change the lab test to
     * @return void
     */
    public function editCost($cost)
    {
        if (! is_numeric($cost)) {
            throw new Exception("The lab name (" . $cost . ") must be a number");
        }

        if (! $this->labTestInDatabase()) {
            throw new Exception("The lab test (" . $this->name . ") is NOT in the database");
        }

        $lab_test_id = $this->getLabTestId();

        $query = "UPDATE `LabTests` SET `Cost` = :Cost WHERE `LabTestId` = :LabTestId;";
        $stmt_edit_name = $this->con->prepare($query);
        $stmt_edit_name->bindParam(":Cost", $cost);
        $stmt_edit_name->bindParam(":LabTestId", $lab_test_id);
        $stmt_edit_name->execute();

        $this->cost = $cost;
    }

    /**
     * Deletes an entire lab from the database (note: use extreme caution as this will cascade)
     * @return void
     */
    public function delete()
    {
        if (! $this->labTestInDatabase()) {
            throw new Exception("The lab attempting to be deleted does not exist");
        }

        $lab_test_id = $this->getLabTestId();
        $query = "DELETE FROM `LabTests` WHERE `LabTestId` = :LabTestId;";
        $stmt_delete_lab = $this->con->prepare($query);
        $stmt_delete_lab->bindParam(":LabTestId", $lab_test_id);
        $stmt_delete_lab->execute();
    }

    
    /**
     * Adds a component to the current lab test (intelligently adds labs and lab components not already in the database)
     * @return void
     */
    public function addComponent(LabComponent $lab_component)
    {
        // Lab component is already a part of the lab test, don't duplicate
        if ($this->labHasLabComponent($lab_component)) {
            throw new Exception("This component is already a part of the current lab test");
        }

        // Grab the IDs of the lab test and the component to add
        $lab_test_id = $this->getLabTestId();
        $lab_component_id = $lab_component->getId();
        
        // Insert the lab association
        $query = "INSERT INTO `LabComponentsAssociation` (`LabTestId`, `LabTestComponentId`) VALUES (:LabTestId, :LabTestComponentId);";
        $stmt_add_component = $this->con->prepare($query);
        $stmt_add_component->bindParam(":LabTestId", $lab_test_id);
        $stmt_add_component->bindParam(":LabTestComponentId", $lab_component_id);
        $stmt_add_component->execute();

        // TODO: test
        $this->lab_components[] = $lab_component;
    }

    /**
     * Deletes a component from the current lab test (intelligently adds labs and lab components not already in the database)
     * @return void
     */
    public function removeComponent(LabComponent $lab_component)
    {
        // If the lab test is not already in the database, simply "make it so"
        if (! $this->labTestInDatabase()) {
            throw new Exception("Lab components can't be removed from a lab test not stored in the database");
        }

        // If the lab component is not already in the database, simply "make it so"
        if (! $lab_component->inDatabase()) {
            throw new Exception("Lab component is not in the database and can't be removed from the lab");
        }

        // Lab component is already a part of the lab test, don't duplicate
        if (! $this->labHasLabComponent($lab_component)) {
            throw new Exception("The lab test doesn't have the component that was attempted to be removed");
        }

        // Grab ID's for deletion
        $lab_test_id = $this->getLabTestId();
        $lab_component_id = $lab_component->getId();

        // Query to delete the lab component from the lab test
        $query = "DELETE FROM `LabComponentsAssociation` WHERE `LabTestId` = :LabTestId AND `LabTestComponentId` = :LabTestComponentId;";
        $stmt_delete_component = $this->con->prepare($query);
        $stmt_delete_component->bindParam(":LabTestId", $lab_test_id);
        $stmt_delete_component->bindParam(":LabTestComponentId", $lab_component_id);
        $stmt_delete_component->execute();

        // TODO: test
        unset($this->lab_components[array_search($lab_component)]);
    }
}

//////
// Debugging example: can be run from the terminal with "php <filename>"
//////

// require_once("../includes/db.php");
// spl_autoload_register(function ($class) {
//     include $class . '.php';
// });
// try {
//     $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

// } catch(PDOException $e) {
//     echo "Error: " . $e->getMessage() . "\n";
// }

// $lab = new Lab("Blood Panel", 25.00, $con);

// $lab->store();
// $lab->editName("Ooga");
// $lab->editCost(2);
// $lab->delete();
// $lab->addComponent(new LabComponent("bill", "%", $con));

// print_r($lab->lab_components);
// $lab->removeComponent(new LabComponent("bill", "%", $con));
// print_r($lab->lab_components);
