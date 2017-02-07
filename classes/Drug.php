<?php
require("../includes/db.php");
class Drug
{
    public $generic_name;
    public $brand_name;
    public $formulation;
    public $dose;
    public $unit;
    
    public function __construct($generic_name, $brand_name, $formulation, $dose, $unit, $con)
    {
        // Physician characteristics
        $this->generic_name = $generic_name; 
        $this->brand_name = $brand_name;
        $this->formulation = $formulation;
        $this->dose = $dose;
        $this->unit = $unit;
        
        // Database connection
        $this->con = $con;
    }
    
    public function storeDrug()
    {
        // query for primary key of selected drug units
        $query = "SELECT `DrugNameId` FROM `DrugNames` WHERE `GenericName` = :genericname;";
        $stmt_find_generic_name = $this->con->prepare($query);
        $stmt_find_generic_name->bindParam(":genericname", $this->generic_name);
        $stmt_find_generic_name->execute();
        $generic_name_id = $stmt_find_generic_name->fetch()[0];

        // Unit does not exist --> insert and pull primary key
        if (empty($generic_name_id)) {
			
			$formulation_id = $this->findFormulationId($this->formulation);
			
			$query = "INSERT INTO `DrugNames` (`GenericName`, `BrandName`) VALUES (:generic_name, :brand_name);";
			$stmt_drugnames = $this->con->prepare($query);
			$stmt_drugnames->bindParam(":generic_name", $this->generic_name);
			$stmt_drugnames->bindParam(":brand_name", $this->brand_name);
			$stmt_drugnames->execute();
			
			$unit_id = $this->findUnitId($this->unit);
			
			$query = "INSERT INTO `DrugDoses` (`Dose`, `UnitId`) VALUES (:dose, :unitid);";
			$stmt_drugdoses = $this->con->prepare($query);
			$stmt_drugdoses->bindParam(":dose", $this->dose);
			$stmt_drugdoses->bindParam(":unitid", $unit_id);
			$stmt_drugdoses->execute();
			
			$query = "SELECT `DrugNameId` from `DrugNames` WHERE (`GenericName`) = (:genericname);";
			$stmt_DrugNameId = $this->con->prepare($query);
			$stmt_DrugNameId->bindParam(":genericname", $this->generic_name);
			$stmt_DrugNameId->execute();
			$drug_name_id = $stmt_DrugNameId->fetch()[0];
			
			$drug_dose_id = $this->findDrugDoseId($this->dose, $this->unit);
			
			$query = "INSERT INTO `Drugs` (`FormulationId`, `DrugNameId`, `DrugDoseId`) VALUES (:formulationid, :drugnameid, :drugdoseid);";
			$stmt_drugs = $this->con->prepare($query);
			$stmt_drugs->bindParam(":formulationid", $formulation_id);
			$stmt_drugs->bindParam(":drugnameid", $drug_name_id);
			$stmt_drugs->bindParam(":drugdoseid", $drug_dose_id);
			$stmt_drugs->execute();
		}
	}

    public function deleteDrug($generic_name)
    {
		$query = "DELETE FROM `DrugNames` WHERE `GenericName` = (:genericname);";
		$stmt_deletedrugname = $this->con->prepare($query);
        $stmt_deletedrugname->bindParam(":genericname", $generic_name);
        $stmt_deletedrugname->execute();
    }
    public function editName($newname)
    {
        $query = "SELECT `DrugNameId` from `DrugNames` WHERE (`GenericName`) = (:genericname);";
        $stmt_search = $this->con->prepare($query);
        $stmt_search->bindParam(":genericname", $this->generic_name);
        $stmt_search->execute();
        $drugnameid = $stmt_search->fetch()[0];
		
		$query = "UPDATE `DrugNames` SET `GenericName` = (:newname) WHERE `DrugNameId` = (:drugnameid);";
		$stmt_change_name = $this->con->prepare($query);
        $stmt_change_name->bindParam(":newname", $newname);
        $stmt_change_name->bindParam(":drugnameid", $drugnameid);
        $stmt_change_name->execute();
		
		$this->generic_name = $newname;
    }
	
	private function findFormulationId($formulation)
    {
        // query for primary key of selected drug units
        $query = "SELECT `FormulationId` FROM `Formulations` WHERE `Formulation` = :formulation;";
        $stmt_find_formulation_id = $this->con->prepare($query);
        $stmt_find_formulation_id->bindParam(":formulation", $formulation);
        $stmt_find_formulation_id->execute();
        $formulation_id = $stmt_find_formulation_id->fetch()[0];

        // Unit does not exist --> insert and pull primary key
        if (empty($formulation_id)) {
            // insert new unit into unit table
            $query = "INSERT INTO `Formulations` (`Formulation`) VALUES (:formulation);";
            $stmt_insert_formulation = $this->con->prepare($query);
            $stmt_insert_formulation->bindParam(":formulation", $formulation);
            $stmt_insert_formulation->execute();

            // Rerun query to grab primary key of drug unit
            $stmt_find_formulation_id->execute();
            $formulation_id = $stmt_find_formulation_id->fetch()[0];
        }

        return $formulation_id;
	}
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
	private function findDrugDoseId($dose, $unit)
    {
		$unit_id = $this->findUnitId($unit);
		
        // query for primary key of selected drug units
        $query = "SELECT `DrugDoseId` FROM `DrugDoses` WHERE `Dose` = :dose and `UnitId` = :unitid;";
        $stmt_find_drug_dose_id = $this->con->prepare($query);
        $stmt_find_drug_dose_id->bindParam(":dose", $dose);
		$stmt_find_drug_dose_id->bindParam(":unitid", $unit_id);
        $stmt_find_drug_dose_id->execute();
        $drug_dose_id = $stmt_find_drug_dose_id->fetch()[0];

        // Unit does not exist --> insert and pull primary key
        if (empty($drug_dose_id)) {
            // insert new unit into unit table
            $query = "INSERT INTO `DrugDoses` (`Dose`, `UnitId`) VALUES (:dose, :unitid);";
            $stmt_insert_drugdose = $this->con->prepare($query);
			$stmt_insert_drugdose->bindParam(":dose", $dose);
            $stmt_insert_drugdose->bindParam(":unitid", $unit_id);
            $stmt_insert_drugdose->execute();

            // Rerun query to grab primary key of drug unit
            $stmt_find_drug_dose_id->execute();
            $unit_id = $stmt_find_drug_dose_id->fetch()[0];
        }

        return $drug_dose_id; 
    }
}
try {
    $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$drug = new Drug("citalopram", "Celexa", "Oral",500, "g", $con);
//$drug->storeDrug(); 
//$drug->deleteDrug("acetaminophen");
//$drug->editName("psych");
?>