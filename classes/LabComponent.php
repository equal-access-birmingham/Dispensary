<?php
class LabComponent
{
    public $name;
    public $default_units;

    public function __construct($name, $default_units, $con)
    {
        // LabComponent characteristics
        $this->name = $name;
        $this->default_units = $default_units;
        
        // Database connection
        $this->con = $con;
    }

    /**
     * Adds a new lab component to the database based on the properties of the class
     * @return void
     */
    public function createNew()
    {
        // query for primary key of selected drug units
        $query = "SELECT `UnitId` FROM `Units` WHERE `Unit` = :DefaultUnit;";
        $stmt_find_unit_id = $this->con->prepare($query);
        $stmt_find_unit_id->bindParam(":DefaultUnit", $this->default_units);
        $stmt_find_unit_id->execute();
        $drug_units = $stmt_unit->fetch()[0];

        // Unit does not exist --> insert and pull primary key
        if (empty($drug_units)) {
            // insert new unit into unit table
            $query = "INSERT INTO `Units` (`Unit`) VALUES (:Unit);";
            $stmt_insert_unit = $this->con->prepare($query);
            $stmt_insert_unit->bindParam(":Unit", $this->default_units);
            $stmt_insert_unit->execute();

            // Rerun query to grab primary key of drug unit
            $stmt_find_unit_id->execute();
            $drug_units = $stmt_find_unit_id->fetch()[0];
        }

        // Insert new lab component into database
        $query = "INSERT INTO `LabTestComponents` (`LabTestComponent`, `LabTestComponentDefaultUnitId`) VALUES (:LabTestComponent, :LabTestComponentDefaultUnitId);";
        $stmt_insert_component = $this->con->prepare($query);
        $stmt_insert_component->bindParam(":LabTestComponent", $this->name);
        $stmt_insert_component->bindParam(":LabTestComponentDefaultUnitId", $drug_units);
        $stmt_insert_component->execute();
    }

    // deletes an entry from the database (user beware)
    public function delete()
    {

    }
}
