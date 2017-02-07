<?php
//Matt working on testing this out as of 2/6/17. Should be good but someone else might need to double check/ Tim will need to use the getPatientId function in some of his code to know if it works. -Matt
require_once('../includes/db.php');
class Patient
{
    public $fname;
    public $lname;
    public $dob;
    public $address_street;
    public $phone_number;
    public $email_address;

    public function __construct($fname, $lname, $dob, $address_street, $phone_number, $email_address, $con)
    {                           
        // Patient characteristics
        $this->fname = $fname;    
        $this->lname = $lname;
        $this->dob = $dob;    
        $this->address_street = $address_street;
        $this->phone_number = $phone_number;
        $this->email_address = $email_address;
        
        // Database connection
        $this->con = $con;
    }

//Commented this out for now since we already have this functionality in the Intake Form System
/*
    //creates a new physician in the database

    public function storePatient()
    {
        //The code block below helps ensure we do not input the same patient multiple times into the database. This queries the database for the primary key of the patient based on the user typing in first name, last name, and dob when trying to add a new patient/store a new patient. 
        $query = "SELECT `PatientId` FROM `Patient` WHERE `fname` = :fname AND `lname` = :lname AND `dob` = :dob;";
        $stmt_find_PatientId = $this->con->prepare($query);
        $stmt_find_PatientId->bindParam(":fname", $this->fname);
		$stmt_find_PatientId->bindParam(":lname", $this->lname);
		$stmt_find_PatientId->bindParam(":dob", $this->dob);
        $stmt_find_PatientId->execute();
        $patientid = $stmt_find_PatientId->fetch()[0];
        
        // This says that if the Patients table primary key was not found for the physician be queried for then we can go ahead and start storing the following information into the database about the given physician. 
        if (empty($patientid)) {
            //Inputs all datainto the Patient table
            $query = "INSERT INTO `Patient` (`fname`, `lname`, `dob`, `address_street`, `phone_number`, `email_address`) VALUES (:fname, :lname, :dob, :address_street, :phone_number, :email_address);";
            $stmt_patient = $this->con->prepare($query);
            $stmt_patient->bindParam(":fname", $this->fname);
            $stmt_patient->bindParam(":lname", $this->lname); 
            $stmt_patient->bindParam(":dob", $this->dob);
            $stmt_patient->bindParam(":address_street", $this->address_street); 
            $stmt_patient->bindParam(":phone_number", $this->phone_number); 
            $stmt_patient->bindParam(":email_address", $this->email_address); 
            $stmt_patient->execute();
        }
    }

    //delete a patient from the database. meant for cleaning up mistakes. 
    public function deletePatient()
    {
        $query = "SELECT `PatientId` FROM `Patient` WHERE `fname` = :fname AND `lname` = :lname AND `dob` = :dob;";
        $stmt_find_PatientId = $this->con->prepare($query);
        $stmt_find_PatientId->bindParam(":fname", $this->fname);
        $stmt_find_PatientId->bindParam(":lname", $this->lname);
        $stmt_find_PatientId->bindParam(":dob", $this->dob);
        $stmt_find_PatientId->execute();
        $patientid = $stmt_find_PatientId->fetch()[0];

        $query = "DELETE FROM `Patient` WHERE `PatientId` = :patientid;";
        $stmt_delete_patient = $this->con->prepare($query);
        $stmt_delete_patient->bindParam(":patientid", $patientid);
        $stmt_delete_patient->execute();
    }
*/
    //function to get the patientId from the database
    public function getPatientId()
    {
        $query = "SELECT `PatientId` FROM `Patient` WHERE `fname` = :fname AND `lname` = :lname AND `dob` = :dob;";
        $stmt_find_PatientId = $this->con->prepare($query);
        $stmt_find_PatientId->bindParam(":fname", $this->fname);
        $stmt_find_PatientId->bindParam(":lname", $this->lname);
        $stmt_find_PatientId->bindParam(":dob", $this->dob);
        $stmt_find_PatientId->execute();

        $patientid = $stmt_find_PatientId->fetch()[0];
      
        if (empty($patientid)) {
            throw new Exception("Patient is not in database");
        }

        return $patientid;


    }

}

/*
//Testing Code Below
try {
    $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

 } catch(PDOException $e) {
     echo "Error: " . $e->getMessage() . "\n";
 }


$patient = new Patient("Matthew", "Hess", "12-28-1991", "2726 Caldwell Avenue South", "7062884070", "matthess@uab.edu", $con);
    echo $patient->fname . "\n";
    echo $patient->lname . "\n";
    echo $patient->dob . "\n";
    echo $patient->address_street . "\n";
    echo $patient->phone_number . "\n";
    echo $patient->email_address . "\n";

    $patient->getPatientId();
*/


