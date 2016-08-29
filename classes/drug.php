<?php
class Drug
{
    public $generic_name;
    public $brand_name;
    public $formulation;
    public $dose;
    public $dose_units;
    public $stock;
    public $cost;
    public $minimum_stock_needed;
    public $expiration_date;

    
    private function __construct($generic_name, $brand_name, $formulation, $dose, $dose_units, $stock, $cost, $minimum_stock_needed, $expiration_date, $con)
    {
        // Physician characteristics
        $this->generic_name = $generic_name;
        $this->brand_name = $brand_name;
        $this->formulation = $formulation;
        $this->dose = $dose;
        $this->dose_units = $dose_units;
        $this->stock = $stock;
        $this->cost = $cost;
        $this->minimum_stock_needed = $minimum_stock_needed;
        $this->expiration_date = $expiration_date; 
        
        // Database connection
        $this->con = $con;
    }
    
    public function createNew()
    {
        $query = "INSERT INTO `Formulations` (`Formulation`) VALUES (:formulation);";
        $stmt_formulations = $this->$con->prepare($query);
        $stmt_formulations->bindParam(":formulation", $this->formulation);
        $stmt_formulations->execute();
        $stmt_formulations->store_result();
        $stmt_formulations->close();
        
        $query = "INSERT INTO `DrugNames` (`GenericName`, `BrandName`) VALUES (:generic_name, :brand_name);";
        $stmt_drugnames = $this->$con->prepare($query);
        $stmt_drugnames->bindParam(":generic_name", $this->generic_name);
        $stmt_drugnames->bindParam(":brand_name", $this->brand_name);
        $stmt_drugnames->execute();
        $stmt_drugnames->close();
        
        $query = "INSERT INTO `Units` (`Unit`) VALUES (?);";
        $stmt_units = $this->$con->prepare($query);
        $stmt_units->bindParam(":unit", $this->unit);
        $stmt_units->execute();
        $stmt_units->store_result();
        $stmt_units->close();
        
        $query = "SELECT `UnitId` from `Units` WHERE (`Unit`) = (?);";
        $stmt_UnitId = $this->$con->prepare($query);
        $stmt_UnitId->bindParam(":unit", $this->unit);
        $stmt_UnitId->execute();
        $stmt_UnitId->store_result();
        $stmt_UnitId->bind_result($this->unitid);
        $stmt_UnitId->fetch();
        $stmt_UnitId->close();
        
        $query = "INSERT INTO `DrugDoses` (`Dose`, `DoseUnitId`) VALUES (?, ?);";
        $stmt_drugdoses = $this->$con->prepare($query);
        $stmt_drugdoses->bindParam(":dose", $this->dose);
        $stmt_drugdoses->bindParam(":dose", $this->unitid);
        $stmt_drugdoses->execute();
        $stmt_drugdoses->store_result();
        $stmt_drugdoses->close();
        
        $query = "SELECT `FormulationId` from `Formulations` WHERE (`Formulation`) = (?);";
        $stmt_FormulationId = $this->$con->prepare($query);
        $stmt_FormulationId->bindParam(":formulation", $this->formulation);
        $stmt_FormulationId->execute();
        $stmt_FormulationId->store_result();
        $stmt_FormulationId->bind_result($this->formulationid);
        $stmt_FormulationId->fetch();
        $stmt_FormulationId->close();
        
        $query = "SELECT `DrugNameId` from `DrugNames` WHERE (`GenericName`) = (?);";
        $stmt_DrugNameId = $this->$con->prepare($query);
        $stmt_DrugNameId->bindParam(":genericname", $this->genericname);
        $stmt_DrugNameId->execute();
        $stmt_DrugNameId->store_result();
        $stmt_DrugNameId->bind_result($this->drugnameid);
        $stmt_DrugNameId->fetch();
        $stmt_DrugNameId->close();
        
        $query = "SELECT `DrugDoseId` from `DrugDoses` WHERE (`Dose`, DoseUnitId) = (?, ?);";
        $stmt_DrugDoseId = $this->$con->prepare($query);
        $stmt_DrugDoseId->bindParam(":dose", $this->dose);
        $stmt_DrugDoseId->execute();
        $stmt_DrugDoseId->store_result();
        $stmt_DrugDoseId->bind_result($this->drugdoseid);
        $stmt_DrugDoseId->fetch();
        $stmt_DrugDoseId->close();
        
        $query = "INSERT INTO `Drugs` (`FormulationId`, `DrugNameId`, `DrugDoseId`, `DoseUnitId`) VALUES (?, ?, ?, ?);";
        $stmt_drugs = $this->$con->prepare($query);
        $stmt_drugs->bindParam(":formulationid", $this->formulationid);
        $stmt_drugs->bindParam(":drugnameid", $this->drugnameid);
        $stmt_drugs->bindParam(":drugdoseid", $this->drugdoseid);
        $stmt_drugs->bindParam(":doseunitid", $this->doseunitid);
        $stmt_drugs->execute();
        $stmt_drugs->store_result();
        $stmt_drugs->close();
        
    }

    public function deleteDrug()
    {

    }

    public function editName()
    {
        
    }

    public function changeStock()
    {

    }

    public function changeExpirationDate()
    {

    }

    public function changeCost()
    {

    }

    public function changeMinimumStockNeeded()
    {

    }

    // Search terms to consider
    // generic name
    // brand name
    // Note:  might be better in Stock object
    public function lookupDrug()
    {

    }
}