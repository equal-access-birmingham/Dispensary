<?php
/**
 * Establishes a Lab class that interacts with the LabTest table and LabComponentsAssociation
 * This class groups the LabComponents into coherent lab panels that can be ordered
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
     */
    public function __construct($name, $cost, $lab_components, PDO $con)
    {
        if (! is_string($name) && ! is_numeric($cost) && ! is_array($lab_components)) {
            throw new Exception("The lab name must be a string; the cost must be a number; and the lab components must be an array of LabComponents objects");
        }

        // Create lab name
        $this->name = $name;

        // load components
        $this->cost = $cost;
        $this->lab_components = $lab_components;
        
        // Database connection
        $this->con = $con;
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
    private function labComponentAssociationInDatabase(LabComponent $lab_component)
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
            throw new Exception("The lab test is not stored in the database");
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

        $lab_test_id->getLabTestId();

        $query = "SELECT `LabComponentAssociationId` FROM `LabComponentsAssocations` WHERE `LabTestId` = :LabTestId;";
        $stmt_lab_components_id = $this->con->prepare($query);
        $stmt_lab_components_id->bindParam(":LabTestId", $lab_test_id);
        $stmt_lab_components_id->execute();
        return $stmt_lab_components_id->fetchAll();
    }

    /**
     * Creates a lab test from the Lab object by storing the lab name in the LabTest and associating the lab test with the provided lab components
     * @return void
     */
    public function store()
    {
        // Lab test is already in the database and should not be allowed to be created again
        // Editing is the better route is lab components need to be modified
        if ($this->labTestInDatabase()) {
            throw new Exception("This lab test is already in the database");
        }

        // Inserts a new lab test
        $query = "INSERT INTO `LabTests` (`LabTest`, `Cost`) VALUES (:LabTest, :Cost);";
        $stmt_create_lab = $this->con->prepare($query);
        $stmt_create_lab->bindParam(":LabTest", $this->name);
        $stmt_create_lab->bindParam(":Cost", $this->cost);
        $stmt_create_lab->execute();
        
        // Query to insert lab association in the database
        $query = "INSERT INTO `LabComponentsAssociation` (`LabTestId`, `LabTestComponentId`) VALUES (:LabTestId, :LabTestComponentId);";
        $stmt_lab_association = $this->con->prepare($query);
        $stmt_lab_association->bindParam(":LabTestId", $lab_test_id);
        $stmt_lab_association->bindParam(":LabTestComponentId", $lab_test_component_id);

        // Associate the lab components with the lab test
        foreach ($this->lab_components as $lab_component) {
            if (! $lab_component->inDatabase()) {
                $lab_component->store();
            }

            // variables for the prepared query to insert the lab association
            $lab_test_id = $this->getLabTestId();
            $lab_test_component_id = $lab_component->getId();

            // As long as the association does not already exist in the database, create it
            if (! $this->labComponentAssociationInDatabase($lab_component)) {
                $stmt_lab_association->execute();
            }
        }
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
        if ($this->labComponentAssociationInDatabase($lab_component)) {
            throw new Exception("This component is already a part of the current lab test");
        }

        // If the lab test is not already in the database, simply "make it so"
        if (! $this->labTestInDatabase()) {
            $this->store();
        }

        // If the lab component is not already in the database, simply "make it so"
        if (! $lab_component->inDatabase()) {
            $lab_component->store();
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
        if (! $this->labComponentAssociationInDatabase($lab_component)) {
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
    }
}

//////
// Debugging example: can be run from the terminal with "php <filename>"
//////

// require_once("../includes/db.php");
// try {
//     $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

// } catch(PDOException $e) {
//     echo "Error: " . $e->getMessage() . "\n";
// }

// $lab_components = array(new LabComponent("hct", "mg", $con), new LabComponent("Hb", "mg/dL", $con), new LabComponent("RBC", "", $con));
// $lab = new Lab("Blood Panel", 25.00, $lab_components, $con);

// foreach ($lab->lab_components as $lab_component) {
//     echo "Lab component: ". $lab_component->name . "\n";
// }

// $lab->store();
// $lab->delete();
// $lab->addComponent(new LabComponent("bill", "%", $con));
// $lab->removeComponent(new LabComponent("bill", "%", $con));