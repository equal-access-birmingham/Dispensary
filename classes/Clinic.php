<?php

//All functions for Clinics Class tested and functioning well as of 2/7/17 -Matt

require_once('../includes/db.php');
class Clinics
{
    public $name;
    public $address;
    public $city;
    public $state;
    public $zip_code;
    public $url;
    public $phone_number;
	public $email;


    public function __construct($name, $address, $city, $state, $zip_code, $url, $phone_number, $email, $con)
    {

        // Clinic characteristics
        $this->name = $name;
        $this->address = $address;
        $this->city = $city;
        $this->state = $state;
        $this->zip_code = $zip_code;
        $this->url = $url;
        $this->phone_number = $phone_number;
		$this->email = $email;
        
        // Database connection
        $this->con = $con;
    }
	
    //creates a new clinic in the database
    public function storeClinic()
    {
        //The code block below helps ensure we do not input the same clinic multiple times into the database. This queries the database for the primary key of the clinic based on the user typing in name and address when trying to add a new clinic. 
        $query = "SELECT `ClinicId` FROM `Clinics` WHERE `Name` = :name;";
        $stmt_find_ClinicId = $this->con->prepare($query);
        $stmt_find_ClinicId->bindParam(":name", $this->name);
        $stmt_find_ClinicId->execute();
        $clinicid = $stmt_find_ClinicId->fetch()[0];
        
        // This says that if the Clinic table primary key was not found for the clinic being queried for then we can go ahead and start storing the following information into the database about the new clinic. 
        if (empty($clinicid)) {
            //Inputs all datainto the Clinic table
            $query = "INSERT INTO `Clinics` (`Name`, `Address`, `City`, `State`, `ZipCode`, `URL`, `PhoneNumber`, `Email`) VALUES (:name, :address, :city, :state, :zipcode, :url, :phonenumber, :email);";
            $stmt_clinic = $this->con->prepare($query);
            $stmt_clinic->bindParam(":name", $this->name);
            $stmt_clinic->bindParam(":address", $this->address); 
            $stmt_clinic->bindParam(":city", $this->city);
            $stmt_clinic->bindParam(":state", $this->state); 
            $stmt_clinic->bindParam(":zipcode", $this->zip_code); 
            $stmt_clinic->bindParam(":url", $this->url); 
            $stmt_clinic->bindParam(":phonenumber", $this->phone_number); 
			$stmt_clinic->bindParam(":email", $this->email); 
            $stmt_clinic->execute();
        }
    }

    //delete a clinic from the database. meant for cleaning up mistakes. Clinic is selected from a list pulled up from the LookupClinic object
    public function deleteClinic()
    {
        $query = "SELECT `ClinicId` FROM `Clinics` WHERE `Name` = :name;";
        $stmt_find_ClinicId = $this->con->prepare($query);
        $stmt_find_ClinicId->bindParam(":name", $this->name);
        $stmt_find_ClinicId->execute();
        $clinicid = $stmt_find_ClinicId->fetch()[0];

        $query = "DELETE FROM `Clinics` WHERE `ClinicId` = :clinicid;";
		$stmt_deletephysician = $this->con->prepare($query);
        $stmt_deletephysician->bindParam(":clinicid", $clinicid);
        $stmt_deletephysician->execute();
    }
	
	//Update clinic information, to change details about the clinic - could be useful if the website/email/etc change
	public function updateClinicName($new_name, $new_address, $new_city, $new_state, $new_zip_code, $new_url, $new_phone_number, $new_email)
	{

        $query = "SELECT `ClinicId` from `Clinics` WHERE `Name` = :name AND `Address` = :address AND `City` = :city AND `State` = :state AND `ZipCode` = :zipcode AND `URL` = :url AND `PhoneNumber` = :phonenumber AND `Email` = :email;";
        $stmt_find_ClinicId = $this->con->prepare($query);
        $stmt_find_ClinicId->bindParam(":name", $this->name);
        $stmt_find_ClinicId->bindParam(":address", $this->address);
        $stmt_find_ClinicId->bindParam(":city", $this->city);
        $stmt_find_ClinicId->bindParam(":state", $this->state);
        $stmt_find_ClinicId->bindParam(":zipcode", $this->zip_code);
        $stmt_find_ClinicId->bindParam(":url", $this->url);
        $stmt_find_ClinicId->bindParam(":phonenumber", $this->phone_number);
        $stmt_find_ClinicId->bindParam(":email", $this->email);
        $stmt_find_ClinicId->execute();
        $clinic_identity = $stmt_find_ClinicId->fetch()[0];

        $query = "UPDATE `Clinics` SET `Name` = (:name), `Address` = (:address), `City` = (:city), `State` = (:state), `ZipCode` = (:zipcode), `Url` = (:url), `PhoneNumber` = (:phonenumber), `Email` = :email WHERE `ClinicId` = (:clinic_identity);";
        $stmt_editClinic=$this->con->prepare($query);
        $stmt_editClinic->bindParam(":name", $new_name);
        $stmt_editClinic->bindParam(":address", $new_address); 
        $stmt_editClinic->bindParam(":city", $new_city);
        $stmt_editClinic->bindParam(":state", $new_state); 
        $stmt_editClinic->bindParam(":zipcode", $new_zip_code); 
        $stmt_editClinic->bindParam(":url", $new_url); 
        $stmt_editClinic->bindParam(":phonenumber", $new_phone_number);
		$stmt_editClinic->bindParam(":email", $new_email);
        $stmt_editClinic->bindParam(":clinic_identity", $clinic_identity);
        $stmt_editClinic->execute(); 

        $this->name = $new_name;
        $this->address = $new_address;
        $this->city = $new_city;
        $this->state = $new_state;
        $this->zip_code = $new_zip_code;
        $this->url = $new_url;
		$this->phone_number = $new_phone_number;
        $this->email = $new_email;
	}
}
/*
//test code below
try {
    $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$clinic = new Clinics("HappyLand", "100 Optimist Ave", "Joyville", "AL", "00000", "www.lol.com", "123-456-7890", "hello@gmail.com", $con);
//echo $clinic ->name . "\n"; 
//echo $clinic ->address . "\n";
//echo $clinic ->city . "\n";
//echo $clinic ->state . "\n";
//echo $clinic ->zip_code . "\n";
//echo $clinic ->url . "\n";
//echo $clinic ->phone_number . "\n";
//echo $clinic ->email . "\n";
//All of these were able to be echo'd 2/7/17 -Matt

//$clinic->storeClinic();   //tested well 2/7/17
//$clinic->deleteClinic();  //tested well 2/7/17
//$clinic->updateClinicName("SadLand", "101 Super Sad Lane", "Lameland", "MS", "35205", "www.rofl.com", "098-765-4321", "goodbye@gmail.com"); //tested well 2/7/17
*/
?>