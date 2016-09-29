<?php
class LabComponent
{
    public $name;
    public $default_units;

    /**
     * Initializes the LabComponent object by giving it a name, default units, and a database connection
     * @param string $name The name of the lab component
     * @param string $default_units The default units that lab will be measured in
     * @param PDO $con The database connection for storing the LabComponent information
     */
    public function __construct($name, $default_units, PDO $con)
    {
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
     * Helper function that finds the database primary key for a provided unit measure
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
        $unit_id = $stmt_unit->fetch()[0];

        return $unit_id;
    }

    /**
     * Adds a new lab component to the database based on the properties of the class
     * @return void
     */
    public function store()
    {
        // Check for duplicate
        $query = "SELECT COUNT(*) FROM `LabTestComponents` WHERE `LabTestComponent` = :LabTestComponent;";
        $stmt_check_duplicate = $this->con->prepare($query);
        $stmt_check_duplicate->bindParam(":LabTestComponent", $this->default_units);
        $stmt_check_duplicate->execute();
        $lab_component_count = $stmt_check_duplicate->fetch()[0];

        // Duplicate found (this could be handled with an exception instead, we'll see later on)
        if ($lab_component_count > 0) {
            echo $this->name . " is already in the database";
            return;
        }

        // query for primary key of selected drug units
        $drug_units = $this->findUnitId($this->default_units);

        // Unit does not exist --> insert and pull primary key
        if (empty($drug_units)) {
            // insert new unit into unit table
            $query = "INSERT INTO `Units` (`Unit`) VALUES (:Unit);";
            $stmt_insert_unit = $this->con->prepare($query);
            $stmt_insert_unit->bindParam(":Unit", $this->default_units);
            $stmt_insert_unit->execute();

            // Rerun query to grab primary key of drug unit
            $drug_units = $this->findUnitId($this->default_units);
        }

        // Insert new lab component into database
        $query = "INSERT INTO `LabTestComponents` (`LabTestComponent`, `LabTestComponentDefaultUnitId`) VALUES (:LabTestComponent, :LabTestComponentDefaultUnitId);";
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
        // find primay key of lab component units
        $drug_units = $this->findUnitId($this->default_units);

        // Query to delete lab component
        $query = "DELETE FROM `LabTestComponents` WHERE `LabTestComponent` = :LabTestComponent AND `LabTestComponentDefaultUnitId` = :LabTestComponentDefaultUnitId;";
        $stmt_delete_component = $this->con->prepare($query);
        $stmt_delete_component->bindParam(":LabTestComponent", $this->name);
        $stmt_delete_component->bindParam(":LabTestComponentDefaultUnitId", $drug_units);
        $stmt_delete_component->execute();
    }

    public function editName()
    {

    }

    public function editDefaultUnits()
    {

    }
}
