<?php
require_once('includes/db.php');

class Physician
{
 //Sushma is working on this.

	public $first_name;
	public $middle_name;
	public $last_name;
	public $suffix;
	public $phone_number;
	public $email;
	public $specialty;

	public $con;

	private function __construct($first_name, $middle_name, $last_name, $suffix, $phone_number, $email, $specialty, $con)
	{
		// Physician characteristics
		$this->first_name = $first_name;
		$this->middle_name = $middle_name;
		$this->last_name = $last_name;
		$this->suffix = $suffix;
		$this->phone_number = $phone_number;
		$this->email = $email;
		$this->specialty = $specialty;
		
		// Database connection
		$this->con = $con;
	}

	//creates a new physician in the database
	public function storePhysician()
	{
		
		$this->con = $con;
		
		//should probably check to make sure object was constructed properly 
	
		$query = "INSERT INTO `Specialties` (`Specialty`) VALUES (:specialty);";
		$stmt_specialties = $this->$con->prepare($query);
		$stmt_specialties->bindParam(":specialty", $this->specialty);
		$stmt_specialties->execute();
		$stmt_specialties->close();
	
		$query = "SELECT `SpecialtyId` FROM `Specialties` WHERE (`Specialty`) = (:specialty);";
		$stmt_SpecialtyId = $this->$con->prepare($query);
		$stmt_SpecialtyId->bindParam(":specialty", $this->specialty);
		$stmt_SpecialtyId->execute();
		$specialty_id = $stmt_SpecialtyId->fetch()[0];
		$stmt_SpecialtyId->close();
		
		$query = "INSERT INTO `Physicians` (`FirstName` `MiddleName` `LastName` `Suffix` `PhoneNumber` `Email` `Specialty`) VALUES (:first_name, :middle_name, :last_name, :suffix, :phone_number, :email, :specialty);";
		$stmt_physician = $this->con->prepare($query);
		$stmt_physician->bindParam(":first_name", $this->first_name);
		$stmt_physician->bindParam(":middle_name", $this->middle_name); 
		$stmt_physician->bindParam(":last_name", $this->last_name);
		$stmt_physician->bindParam(":suffix", $this->suffix); 
		$stmt_physician->bindParam(":phone_number", $this->phone_number); 
		$stmt_physician->bindParam(":email", $this->email); 
		$stmt_physician->bindParam(":specialty", $this->$specialty_id); 
		$stmt_physician->execute(); 
		$stmt_physcian->close(); 
			
	}
	// delete a physician from the database, meant for cleaning up mistakes, physician selected from a list pulled up from the LookupPhysician object
	public function deletePhysician()
	{
		$query = "DELETE FROM `Physicians` WHERE `first_name` = :first_name AND `middle_name`= :middle_name AND`last_name`= :last_name AND `suffix`=:suffix AND `phone_number`=:phone_number AND `email`= :email AND `specialty`=:specialty";
		$stmt_deletePhysician=$this->con->prepare($query); 
		$stmt_deletePhysician->bindParam(":first_name", $this->first_name);
		$stmt_deletePhysician->bindParam(":middle_name", $this->middle_name); 
		$stmt_deletePhysician->bindParam(":last_name", $this->last_name);
		$stmt_deletePhysician->bindParam(":suffix", $this->suffix); 
		$stmt_deletePhysician->bindParam(":phone_number", $this->phone_number); 
		$stmt_deletePhysician->bindParam(":email", $this->email); 
		$stmt_deletePhysician->bindParam(":specialty", $this->$specialty_id); 
		$stmt_deletePhysician->execute(); 
		$stmt_deletePhysician->close();
		
	}
    
	// should be able to edit anything,physician selected from a list pulled up from the LookupPhysician object and user can edit any of the fields, row would be written over with new information 
	public function editPhysician()
	{
		$query = "UPDATE `Physicians` SET `FirstName`=:first_name, `MiddleName`=:middle_name, `LastName`=:last_name, `Suffix`=:suffix, `PhoneNumber`=:phone_number, `Email`=:email, `Specialty`=:specialty WHERE ???";
		$stmt_editPhysician=$this->con->prepare($query);
		$stmt_editPhysician->bindParam(":first_name", $this->first_name);
		$stmt_editPhysician->bindParam(":middle_name", $this->middle_name); 
		$stmt_editPhysician->bindParam(":last_name", $this->last_name);
		$stmt_editPhysician->bindParam(":suffix", $this->suffix); 
		$stmt_editPhysician->bindParam(":phone_number", $this->phone_number); 
		$stmt_editPhysician->bindParam(":email", $this->email); 
		$stmt_editPhysician->bindParam(":specialty", $this->$specialty_id);	
		$stmt_editPhysician->execute(); 
		$stmt_editPhysician->close(); 
		
	}
}