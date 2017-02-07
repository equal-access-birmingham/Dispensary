<?php

//Sucessfully error tested on 2/6/2016 by Steve
require_once("Drug.php");
require_once("../includes/db.php");
 
    spl_autoload_register(function ($class_name) {
            include $class_name . '.php';
    });


//Creates the class for looking up a drug. Its only property is $con and it has no other properties.

class LookupDrug
{

    private $con; 

    public function __construct($con)
    {
        $this->con = $con;
    }

    //This function looks up drug by generic name and returns all the stuff we want to know about the physician in multiple tables.
    public function lookupDrug($GenericName)
    {
        $GenericName = $GenericName."%";

        //Be careful that PDO wioll handle these LIKE statements the same way that MYSQLI did. May be a source of errors in the future. 
        $query = "SELECT `DrugNameId`, `GenericName`, `BrandName` FROM `DrugNames` WHERE `GenericName` LIKE :GenericName;";
        $stmt_find_drug = $this->con->prepare($query);
		$stmt_find_drug->bindParam(":GenericName", $GenericName);
        $stmt_find_drug->execute();
        $result = $stmt_find_drug->fetchALL(); // array(array(), array()) $result[0][2]   Returns results as an array or an array of an array aka a Matrix where you use two numbers to find the data. For example [0,2] accesses the first array and the last name of the physician in that first array to return only a last name. But below in $physician_array we deal with this problem. Remember that the function fetch always gives you back an array.

        //This query below allows me to grab the Ids of the drug from the Drugs table by using the DrugNameId stored in both DrugNames and Drugs table. We prepare and bind the parameters for multiple Ids here and are sure to leave the ":" since it will change for each lookup. 
        $query = "SELECT `DrugId`, `FormulationId`, `DrugDoseId` FROM `Drugs` WHERE `DrugNameId` = :DrugNameId;";
		$stmt_find_IDs = $this->con->prepare($query);
        $stmt_find_IDs->bindParam(":DrugNameId", $DrugNameId);

		//This query below allows me to grab the formulation of the drug from the Drugs table by using the FormulationId stored in Drugs table. We prepare and bind the parameters for FormulationId here and are sure to leave the ":" since it will change for each lookup. 
        $query = "SELECT `Formulation` FROM `Formulations` WHERE `FormulationId` = :FormulationId;";
        $stmt_find_formulation = $this->con->prepare($query);
        $stmt_find_formulation->bindParam(":FormulationId", $FormulationId);

		//This query below allows me to grab the dose and unitid of the drug from the Drugs table by using the DrugDoseId stored in Drugs table. We prepare and bind the parameters for DrugDoseId here and are sure to leave the ":" since it will change for each lookup. 
        $query = "SELECT `Dose`, `UnitId` FROM `DrugDoses` WHERE `DrugDoseId` = :DrugDoseId;";
        $stmt_find_dose = $this->con->prepare($query);
        $stmt_find_dose->bindParam(":DrugDoseId", $DrugDoseId);

		//This query below allows me to grab the dose and unitid of the drug from the Drugs table by using the DrugDoseId stored in Drugs table. We prepare and bind the parameters for DrugDoseId here and are sure to leave the ":" since it will change for each lookup. 
        $query = "SELECT `Unit` FROM `Units` WHERE `UnitId` = :UnitId;";
        $stmt_find_unit = $this->con->prepare($query);
        $stmt_find_unit->bindParam(":UnitId", $UnitId);
		
        //drug_array is the array that will be returned. The foreach statement loops through the array created by the fetch statement and recrates the array using the terms we provided like _____
        $drug_array = array();
        foreach ($result as $drug) {
            $DrugNameId = $drug['DrugNameId'];
			$stmt_find_IDs->execute();
			$IDs = $stmt_find_IDs->fetch();
			$DrugId = $IDs[0];
			$FormulationId = $IDs[1];
			$DrugDoseId = $IDs[2];
			
			$stmt_find_formulation->execute();
			$Formulation = $stmt_find_formulation->fetch()[0];
			
			$stmt_find_dose->execute();
			$doseinfo = $stmt_find_dose->fetch();
			$Dose = $doseinfo[0];
			$UnitId = $doseinfo[1];
			
			$stmt_find_unit->execute();
            $Unit = $stmt_find_unit->fetch()[0];
			
            $drug_object = new Drug($drug['GenericName'], $drug['BrandName'], $Formulation, $Dose, $Unit, $this->con);
            $drug_array[] = $drug_object; //Puts the created drug object at the end of the array of drugs.
        }
        return $drug_array;
        
    }

}


//try {
//    $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
//
//} catch(PDOException $e) {
//     echo "Error: " . $e->getMessage() . "\n";
//    }
//
//
//$drug_lookup = new LookupDrug($con);
//$drug_list = $drug_lookup->lookupDrug("l");
//
//foreach ($drug_list as $drug) {
//    echo $drug->generic_name ."\n"; 
//	echo $drug->brand_name ."\n";
//    echo $drug->formulation ."\n";
//    echo $drug->dose ."\n";
//    echo $drug->unit ."\n";




            //Code used to test for errors if need to. Add this within the code of the statement you are testing.
            //if ($stmt_physician->errorInfo() [0]) {
            //    echo $stmt_physician->errorInfo()[2];
            //}
>?