<?php
class Lab
{
    public $lab_name;
    public $cost;
    public $lab_components = array();

    public function __construct($lab_name, $cost, $lab_components)
    {
        // build lab object
		$this->lab_name = $lab_name;
        // load components
        $this->cost = $cost;
        $this->lab_components = $lab_components;
        
        // Database connection
        $this->con = $con;
    }

    // creates a new lab in the database and adds necessary lab components (creates relationship)
    public function createLab()
    {
        $query = "INSERT INTO `LabNames` (`LabName`) VALUES (:lab_name);";
        $stmt_labnames = $this->$con->prepare($query);
        $stmt_labnames->bindParam(":lab_name", $this->lab_name);
        $stmt_labnames->execute();
        $stmt_labnames->close();
	    
		$query = "INSERT INTO `Costs` (`Cost`) VALUES (:cost);";
        $stmt_costs = $this->$con->prepare($query);
        $stmt_costs->bindParam(":cost", $this->cost);
        $stmt_costs->execute();
        $stmt_costs->close();
	
	// TODO: replace lab_component1 and lab_component2 with the actual lab components!!!!!!!!!!!!!!!!
	    $query = "INSERT INTO `LabComponents` (`LabComponent1`, `LabComponent2`) VALUES (:lab_component1, :lab_component2);";
        $stmt_labcomponents = $this->$con->prepare($query);
        $stmt_labcomponents->bindParam(":lab_component1", $this->lab_component1);
		$stmt_labcomponents->bindParam(":lab_component2", $this->lab_component2);
        $stmt_labcomponents->execute();
        $stmt_labcomponents->close();
    }

    // Removes lab from database, probably only remove relationship between components
    public function deleteLab()
    {
		$query = "DELETE FROM `LabNames` WHERE `LabName` = (:lab_name);";
		$stmt_deletelabname = $this->$con->prepare($query);
        $stmt_deletelabname->bindParam(":lab_name", $lab_name);
        $stmt_deletelabname->execute();
        $stmt_deletelabname->close();
    }

    // add relationship between lab and component that already exists
    public function addComponent()
    {
        // $component = new LabComponent;
    }

    // removes relationship between lab and component
    public function removeComponent()
    {

    }
}