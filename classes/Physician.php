<?php

//Matt has finished and test this. It is fully functional as of 12/8/2016. Need to still go back through and comment everything well. 

require_once('../includes/db.php');
class Physician
{
    public $first_name;
    public $middle_name;
    public $last_name;
    public $suffix;
    public $phone_number;
    public $email;
    public $specialty;

    private function findSpecialtyId($specialty)
    {
        // query for primary key of selected specialty
        $query = "SELECT `SpecialtyId` FROM `Specialties` WHERE `Specialty` = :specialty;";
        $stmt_find_specialty_id = $this->con->prepare($query);
        $stmt_find_specialty_id->bindParam(":specialty", $specialty);
        $stmt_find_specialty_id->execute();
        $specialty_id = $stmt_find_specialty_id->fetch()[0];

        // Specialty does not exist --> insert and pull primary key
        if (empty($specialty_id)) {
            // insert new unit into unit table
            $query = "INSERT INTO `Specialties` (`Specialty`) VALUES (:specialty);";
            $stmt_insert_specialty = $this->con->prepare($query);
            $stmt_insert_specialty->bindParam(":specialty", $specialty);
            $stmt_insert_specialty->execute();

            // Rerun query to grab primary key of specialty
            $stmt_find_specialty_id->execute();
            $specialty_id = $stmt_find_specialty_id->fetch()[0];
        }

        return $specialty_id;
    }


    public function __construct($first_name, $middle_name, $last_name, $suffix = null, $phone_number, $email, $specialty, $con)
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

    public function getId()
    {
        $query = "SELECT `PhysicianId` FROM `Physician` WHERE `FirstName` = :FirstName AND `MiddleName` = :MiddleName AND `LastName` = :LastName AND `Suffix` = :Suffix AND `PhoneNumber` = :PhoneNumber AND `Email` = :Email;";
        $stmt_get_id = $this->con->prepare($query);
        $stmt_get_id->bindParam(":FirstName", $this->first_name);
        $stmt_get_id->bindParam(":MiddleName", $this->middle_name);
        $stmt_get_id->bindParam(":LastName", $this->last_name);
        $stmt_get_id->bindParam(":Suffix", $this->suffix);
        $stmt_get_id->bindParam(":PhoneNumber", $this->phone_number);
        $stmt_get_id->bindParam(":Email", $this->email);
        $stmt_get_id->execute();
        
        $id = $stmt_get_id->fetch()[0];

        if (empty($id)) {
            throw new Exception("Physician is not in database");
        }

        return $id;
    }

    //creates a new physician in the database
    public function storePhysician()
    {
        //The code block below helps ensure we do not input the same physician multiple times into the database. This queries the database for the primary key of the physician based on the user typing in first name, last name, and email when trying to add a new physician/store a new physician. 
        $query = "SELECT `PhysicianId` FROM `Physicians` WHERE `FirstName` = :firstname AND `LastName` = :lastname AND `Email` = :email;";
        $stmt_find_PhysicianId = $this->con->prepare($query);
        $stmt_find_PhysicianId->bindParam(":firstname", $this->first_name);
        $stmt_find_PhysicianId->bindParam(":lastname", $this->last_name);
        $stmt_find_PhysicianId->bindParam(":email", $this->email);
        $stmt_find_PhysicianId->execute();
        $physician_identity = $stmt_find_PhysicianId->fetch()[0];

        // This says that if the Physicians table primary key was not found for the physician be queried for then we can go ahead and start storing the following information into the database about the given physician. 
        if (empty($physician_identity)) {
            $specialty_id = $this->findSpecialtyId($this->specialty);

            //Says to input all the other data the user puts in about the physician into the Physician table
            $query = "INSERT INTO `Physicians` (`FirstName`, `MiddleName`, `LastName`, `Suffix`, `PhoneNumber`, `Email`, `SpecialtyId`) VALUES (:first_name, :middle_name, :last_name, :suffix, :phone_number, :email, :specialtyid);";
            $stmt_physician = $this->con->prepare($query);
            $stmt_physician->bindParam(":first_name", $this->first_name);
            $stmt_physician->bindParam(":middle_name", $this->middle_name); 
            $stmt_physician->bindParam(":last_name", $this->last_name);
            $stmt_physician->bindParam(":suffix", $this->suffix); 
            $stmt_physician->bindParam(":phone_number", $this->phone_number); 
            $stmt_physician->bindParam(":email", $this->email); 
            $stmt_physician->bindParam(":specialtyid", $specialty_id); 
            $stmt_physician->execute();

        }
    }

    //delete a physician from the database. meant for cleaning up mistakes. Physician selected from a list pulled up from the LookupPhysician object
    public function deletePhysician()
    {
        $query = "SELECT `PhysicianId` FROM `Physicians` WHERE `FirstName` = :firstname AND `LastName` = :lastname AND `Email` = :email;";
        $stmt_find_PhysicianId = $this->con->prepare($query);
        $stmt_find_PhysicianId->bindParam(":firstname", $this->first_name);
        $stmt_find_PhysicianId->bindParam(":lastname", $this->last_name);
        $stmt_find_PhysicianId->bindParam(":email", $this->email);
        $stmt_find_PhysicianId->execute();
        $physician_identity = $stmt_find_PhysicianId->fetch()[0];

        $query = "DELETE FROM `Physicians` WHERE `PhysicianId` = :physicianid;";
        $stmt_deletephysician = $this->con->prepare($query);
        $stmt_deletephysician->bindParam(":physicianid", $physician_identity);
        $stmt_deletephysician->execute();
    }


    // should be able to edit anything,physician selected from a list pulled up from the LookupPhysician object and user can edit any of the fields, row would be written over with new information 
    // Matt made edits to this part in particular on 11/8/16 after hackathon. Will need Tim adn Steve to look over in depth. Maybe lots of errors. 
    public function editPhysician($new_first_name, $new_middle_name, $new_last_name, $new_suffix, $new_phone_number, $new_email, $new_specialty)
    {
        $query = "SELECT `PhysicianId` from `Physicians` WHERE `FirstName` = :firstname AND `MiddleName` = :middlename AND `LastName` = :lastname AND `Suffix` = :suffix AND `PhoneNumber` = :phonenumber AND `Email` = :email;";
        $stmt_find_PhysicianId = $this->con->prepare($query);
        $stmt_find_PhysicianId->bindParam(":firstname", $this->first_name);
        $stmt_find_PhysicianId->bindParam(":middlename", $this->middle_name);
        $stmt_find_PhysicianId->bindParam(":lastname", $this->last_name);
        $stmt_find_PhysicianId->bindParam(":suffix", $this->suffix);
        $stmt_find_PhysicianId->bindParam(":phonenumber", $this->phone_number);
        $stmt_find_PhysicianId->bindParam(":email", $this->email);
        $stmt_find_PhysicianId->execute();
        $physician_identity = $stmt_find_PhysicianId->fetch()[0];

        $specialty_id = $this->findSpecialtyId($new_specialty);

        $query = "UPDATE `Physicians` SET `FirstName` = (:first_name), `MiddleName` = (:middle_name), `LastName` = (:last_name), `Suffix` = (:suffix), `PhoneNumber` = (:phone_number), `Email` = (:email), `SpecialtyId` = (:specialty) WHERE `PhysicianId` = (:physician_identity);";
        $stmt_editPhysician=$this->con->prepare($query);
        $stmt_editPhysician->bindParam(":first_name", $new_first_name);
        $stmt_editPhysician->bindParam(":middle_name", $new_middle_name); 
        $stmt_editPhysician->bindParam(":last_name", $new_last_name);
        $stmt_editPhysician->bindParam(":suffix", $new_suffix); 
        $stmt_editPhysician->bindParam(":phone_number", $new_phone_number); 
        $stmt_editPhysician->bindParam(":email", $new_email); 
        $stmt_editPhysician->bindParam(":specialty", $specialty_id);
        $stmt_editPhysician->bindParam(":physician_identity", $physician_identity);
        $stmt_editPhysician->execute(); 

        $this->first_name = $new_first_name;
        $this->middle_name = $new_middle_name;
        $this->last_name = $new_last_name;
        $this->suffix = $new_suffix;
        $this->phone_number = $new_phone_number;
        $this->email = $new_email;
        $this->specialty = $new_specialty;
        
    }
}

//Test Code Below to test all functions. All functions tested on 12/8/16 and fully functionl.
//try {
//    $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

// } catch(PDOException $e) {
//     echo "Error: " . $e->getMessage() . "\n";
// }

//  $physician = new Physician("Matthew", "Charles", "Hess", "", "706-288-4070", "matthess@uab.edu", "Orthopedic Surgery", $con);
//    echo $physician->first_name . "\n";
//    echo $physician->middle_name . "\n";
//    echo $physician->last_name . "\n";
//   echo $physician->suffix . "\n";
//    echo $physician->phone_number . "\n";
//    echo $physician->email . "\n";
//    echo $physician->specialty . "\n";

    //$physician->storePhysician();
    //$physician->deletePhysician();
    //$physician->editPhysician("Tim", "Irving", "Kennel", "MD PhD", "111-222-3344", "informagician@codingrocks.com", "Pathology Informatics");


            //Code used to test for errors if need to. Add this within the code of the statement you are testing.
            //if ($stmt_physician->errorInfo() [0]) {
            //    echo $stmt_physician->errorInfo()[2];
            //}















