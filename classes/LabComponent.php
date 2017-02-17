<?php
/**
 * LabComponent class that interacts with the LabComponent table in the database to create
 *     individual pieces of lab tests
 * Example: LabTest = Blood Panel; LabComponent = Hgb
 * 
 * Public Method List
 *  - inDatabase
 *  - getId
 *  - store
 *  - delete
 *  - editName
 *  - editDefaultUnits
 * 
 * (Finished and tested)
 */
class LabComponent
{
    /**
     * @var string $name The name of the lab component
     */
    public $name;

    /**
     * @var string $default_units The default units for this lab component to use
     */
    public $default_units;

    /**
     * @var PDO $con Database connection
     */
    private $con;

    /**
     * Initializes the LabComponent object by giving it a name, default units, and a database connection
     * @param string $name The name of the lab component
     * @param string $default_units The default units that lab will be measured in
     * @param PDO $con The database connection for storing the LabComponent information
     * @return void
     */
    public function __construct($name, $default_units, PDO $con)
    {
        // if ($name == null || $default_units == null) {
        //     throw new Exception("Both the lab component name and the default units for the lab must be provided");
        // }

        if (! is_string($name) && ! is_string($default_units)) {
            throw new Exception("Both the lab component name and the default units for the lab must be strings");
        }

        // LabComponent characteristics
        $this->name = $name;
        $this->default_units = $default_units;
        
        // Database connection
        $this->con = $con;
    }

    /**
     * Helper function that finds the database primary key for a provided unit measure and inserts it if not found
     * @param string $unit The unit measure to find the primary key for
     * @return int
     */
    private function findUnitId($unit)
    {
        // query for primary key of selected drug units
        $query = "SELECT `UnitId` FROM `Units` WHERE `Unit` = :Unit;";
        $stmt_find_unit_id = $this->con->prepare($query);
        $stmt_find_unit_id->bindParam(":Unit", $unit);
        $stmt_find_unit_id->execute();
        $unit_id = $stmt_find_unit_id->fetch()[0];

        // Unit does not exist --> insert and pull primary key
        if (empty($unit_id)) {
            // insert new unit into unit table
            $query = "INSERT INTO `Units` (`Unit`) VALUES (:Unit);";
            $stmt_insert_unit = $this->con->prepare($query);
            $stmt_insert_unit->bindParam(":Unit", $unit);
            $stmt_insert_unit->execute();

            // Rerun query to grab primary key of drug unit
            $stmt_find_unit_id->execute();
            $unit_id = $stmt_find_unit_id->fetch()[0];
        }

        return $unit_id;
    }

    /**
     * Verifies that the lab component exists in the database already
     * @return boolean
     */
    public function inDatabase()
    {
        // Query to see if lab component is in the database already
        $query = "SELECT COUNT(*) FROM `LabTestsComponents` WHERE `LabTestComponent` = :LabTestComponent;";
        $stmt_check_duplicate = $this->con->prepare($query);
        $stmt_check_duplicate->bindParam(":LabTestComponent", $this->name);
        $stmt_check_duplicate->execute();
        $lab_component_count = $stmt_check_duplicate->fetch()[0];

        // Lab component already in the database
        if ($lab_component_count > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns the id of the lab component from the database
     * @return int
     */
    public function getId()
    {
        // Lab component is not in the database and therefore has no ID to retrieve
        if (! $this->inDatabase()) {
            throw new Exception("The lab component has not been stored in the database");
        }

        // Query to retrieve the lab test components id
        $query = "SELECT `LabTestComponentId` FROM `LabTestsComponents` WHERE `LabTestComponent` = :LabTestComponent;";
        $stmt_component_id = $this->con->prepare($query);
        $stmt_component_id->bindParam(":LabTestComponent", $this->name);
        $stmt_component_id->execute();
        return $stmt_component_id->fetch()[0];
    }

    /**
     * Adds a new lab component to the database based on the properties of the class
     * @return void
     */
    public function store()
    {
        /**
         * TODO: maybe add automagic storage of "Individual " . $this->name Lab as well for ease
         */

        // Lab component already in database and is a duplicate (this could be handled with an exception instead, we'll see later on)
        if ($this->inDatabase()) {
            throw new Exception($this->name . " is already in the database\n");
        }

        // query for primary key of selected drug units
        $drug_units = $this->findUnitId($this->default_units);

        // Insert new lab component into database
        $query = "INSERT INTO `LabTestsComponents` (`LabTestComponent`, `LabTestComponentDefaultUnitId`) VALUES (:LabTestComponent, :LabTestComponentDefaultUnitId);";
        $stmt_insert_component = $this->con->prepare($query);
        $stmt_insert_component->bindParam(":LabTestComponent", $this->name);
        $stmt_insert_component->bindParam(":LabTestComponentDefaultUnitId", $drug_units);
        $stmt_insert_component->execute();
    }

    /**
     * Deletes the storage of a LabComponent object from the database
     * @return void
     */
    public function delete()
    {
        /**
         * TODO: would need to delete automagic storage of "Individual " . $this->name Lab as well
         */

        if ($this->inDatabase() == false) {
            throw new Exception("The lab component attempting to be deleted does not exist");
        }

        // find primay key of lab component units
        $drug_units = $this->findUnitId($this->default_units);

        // Query to delete lab component
        $query = "DELETE FROM `LabTestsComponents` WHERE `LabTestComponent` = :LabTestComponent AND `LabTestComponentDefaultUnitId` = :LabTestComponentDefaultUnitId;";
        $stmt_delete_component = $this->con->prepare($query);
        $stmt_delete_component->bindParam(":LabTestComponent", $this->name);
        $stmt_delete_component->bindParam(":LabTestComponentDefaultUnitId", $drug_units);
        $stmt_delete_component->execute();
    }

    /**
     * Edits the name of the the lab component to $new_name and set $this->name = $new_name
     * @param string $new_name The new name of the labe component
     */
    public function editName($new_name)
    {
        if (! is_string($new_name)) {
            throw new Exception("The new name of the lab component must be a string");
        }

        if ($this->inDatabase() == false) {
            throw new Exception("The lab component attempting to be edited does not exist");
        }

        $query = "UPDATE `LabTestsComponents` SET `LabTestComponent` = :NewName WHERE `LabTestComponent` = :OldName;";
        $stmt_change_name = $this->con->prepare($query);
        $stmt_change_name->bindParam(":NewName", $new_name);
        $stmt_change_name->bindParam(":OldName", $this->name);
        $stmt_change_name->execute();

        // if ($stmt_change_name->errorInfo()[0]) {
        //     echo $stmt_change_name->errorInfo()[1] . "\n";
        // }

        $this->name = $new_name;
    }

    /**
     * Changes the default units of the lab component to $new_units and sets $this->default_units = $new_units
     * @param string $new_units The new units for the lab component
     */
    public function editDefaultUnits($new_units)
    {
        if (! is_string($new_units)) {
            throw new Exception("The new units must be a string");
        }

        if ($this->inDatabase() == false) {
            throw new Exception("The lab component attempting to be edited does not exist");
        }

        // query for primary key of selected drug units
        $drug_units = $this->findUnitId($new_units);

        // This needs to be an update
        $query = "UPDATE`LabTestsComponents` SET `LabTestComponentDefaultUnitId` = :NewUnits WHERE `LabTestComponent` = :LabComponentTest;";
        $stmt_insert_component = $this->con->prepare($query);
        $stmt_insert_component->bindParam(":NewUnits", $drug_units);
        $stmt_insert_component->bindParam(":LabComponentTest", $this->name);
        $stmt_insert_component->execute();

        $this->default_units = $new_units;
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

// $lab_component = new LabComponent("bill", "%", $con);
// echo $lab_component->name . "\n";
// echo $lab_component->default_units . "\n";
// $lab_component->store();
// $lab_component->editDefaultUnits("mg");
// echo $lab_component->default_units . "\n";