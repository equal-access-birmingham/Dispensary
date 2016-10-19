<?php
//Matt Hess is still working on this page. Class is fully built, now currently in error testing with the section below. I need Sushmas Physician page to be done before I can actually debug this. Current as of 10/18/16.

//Stuff to comment out later on but used for error testing/functionality for now. Will not comment out the physician.php one later.
require_once("Physician.php");
require_once("../includes/db.php");
 
    spl_autoload_register(function ($class_name) {
            include $class_name . '.php';
    });


//Creates the class for looking up a physician. Its only property is $con and it has no other properties.

class LookupPhysician 
{

    private $con; 

    public function __construct($con)
    {
        $this->con = $con;
    }

    //This function looks up physician by first name and/or last name and/or email address and returns all the stuff we want to know about the physician in the Physicians table.
    public function lookupPhysician($FirstName, $LastName, $Email)
    {
        //Be careful that PDO wioll handle these LIKE statements the same way that MYSQLI did. May be a source of errors in the future. 
        $query = "SELECT `PhysicianId`, `FirstName`, `MiddleName`, `LastName`, `Suffix`, `PhoneNumber`, `Email`, `SpecialtyId` FROM `Physicians` WHERE `FirstName` LIKE :FirstName AND `LastName` LIKE :LastName AND `Email` LIKE :Email;";
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(":FirstName", $FirstName);
        $stmt->bindParam(":LastName", $LastName);
        $stmt->bindParam(":Email", $Email);
        $stmt->execute();
        $result = $stmt->fetchArray(); // array(array(), array()) $result[0][2]   Returns results as an array or an array of an array aka a Matrix where you use two numbers to find the data. For example [0,2] accesses the first array and the last name of the physician in that first array to return only a last name. But below in $physician_array we deal with this problem. Remember that the function fetch always gives you back an array.

        //This query below allows me to grab the Specialty of the physician from the Specialties table by using the SpecialtyId stored in both Physicians table and Specialties table. We prepare and bind the parameters for SpecialtyId here and are sure to leave the ":" since it will change for each lookup. We also bind a parameter $specialty_id which creates a further layer of abstraction and carries down below where we use it in the physician object array.
        $query = "SELECT `Specialty` FROM `Specialties` WHERE `SpecialtyId` = :SpecialtyId;";
        $stmt_find_specialty = $this->con->prepare($query);
        $stmt_find_specialty->bindParam(":SpecialtyId", $specialty_id);

        //Physician_array is the array that will be returned. The foreach statement loops through the array created by the fetch statement on line 34 and recrates the array using the terms we provided like `FirstName` instead of `0`
        $physician_array = array();
        foreach ($result as $physician) {
            $specialty_id = $physician[`SpecialtyId`];
            $stmt_find_specialty->execute();
            $specialty = $stmt_find_specialty->fetch()[0];
            $physician_object = new Physician($physician[`FirstName`], $physician[`MiddleName`], $physician[`LastName`], $physician[`Suffix`], $physician[`PhoneNumber`], $physician[`Email`], $specialty, $this->con);
            $physician_array[] = $physician_object; //Puts the created physician object at the end of the array of physicians.
        }
        return $physician_array;
        
    }

}

//Code Below will be deleted once a UI is built. It is purely being used to test different functions and properties created in the this class using the terminal on the server. 
//In the terminal type: php LookupPhysician.php    This will run the code. You will need to create an object below to be used.

$con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

$physician_loopkup = new lookupPhysician ($con);
$physician_list = $physician_lookup->lookupPhysician();

foreach ($physician_list as $physician) {
    echo $physician->first_name;
}

?>