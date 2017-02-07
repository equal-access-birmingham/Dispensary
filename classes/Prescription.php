<?php

require("../includes/db.php");

spl_autoload_register(function ($name) {
    require($name . ".php");
});

class Prescription
{
    public $drug;
    public $physician;
	public $prescription_date;
	public $dispensed_date;
	public $dispenses_allowed;
	public $patient;
	public $clinic;


    public function __construct(Drug $drug, Physician $physician, $prescription_date, $dispensed_date, $dispenses_allowed, Patient $patient, Clinic $clinic, $con) {
        //Prescription Characteristics
		
		$this->drug = $drug;
		$this->physician = $physician;
		$this->prescription_date = $prescription_date;
		$this->dispensed_date = $dispensed_date;
		$this->dispenses_allowed = $dispenses_allowed;
		$this->patient = $patient;
		$this->clinic = $clinic;
		
		// Database connection
        $this->con = $con;
    }
	public function storePrescription()
    {
		$query = "SELECT `PrescriptionId` FROM `Prescriptions` WHERE `DispensesAllowed` = :dispensesallowed AND `DrugId` = :drugid AND `PhysicianId` = :physicianid AND `ClinicId` = :clinicid AND `Date` = :date AND `PatientId` = :patientid;";
        $stmt_find_PrescriptionId = $this->con->prepare($query);
        $stmt_find_PrescriptionId->bindParam(":dispensesallowed", $this->dispenses_allowed);
        $stmt_find_PrescriptionId->bindParam(":drugid", $drugid);
		$stmt_find_PrescriptionId->bindParam(":physicianid", $physicianid);
		$stmt_find_PrescriptionId->bindParam(":clinicid", $clinicid);
		$stmt_find_PrescriptionId->bindParam(":date", $this->prescription_date);
		$stmt_find_PrescriptionId->bindParam(":patientid", $patientid);
        $stmt_find_PrescriptionId->execute();
        $prescriptionid = $stmt_find_PrescriptionId->fetch()[0];
		
		if (empty($prescriptionid)) {
		
			$drugid = $this->FindDrugId();
			$physicianid = $this->FindPhysicianId();
			$clinicid = $this->FindClinicId();
			$drugid = $this->FindDrugId();
			
			//This stores all info except for dispense date in the prescriptions table
			$query = "INSERT INTO `Prescriptions` (`DispensesAllowed`, `DrugId`, `PhysicianId`, `ClinicId`, `Date`, `PatientId`) VALUES (:dispensesallowed, :drugid, :physicianid, :clinicid, :date, :patientid);";
			$stmt_storestock = $this->con->prepare($query);
			$stmt_storestock->bindParam(":dispensesallowed", $this->dispenses_allowed);
			$stmt_storestock->bindParam(":drugid", $drugid);
			$stmt_storestock->bindParam(":physicianid", $physicianid);
			$stmt_storestock->bindParam(":clinicid",$clinicid);
			$stmt_storestock->bindParam(":date",$this->dispensed_date);
			$stmt_storestock->bindParam(":patientid", $patientid);
			$stmt_storestock->execute();
		}
	}
	public function dispense($dispensed_date = null) //if no date dispensed is provided, it will make the value null
    {
		if($dispensed_date = null){$dispensed_date = date("Ymd");} //if no dispensed date is provided it will use current date
		
		$drugid = $this->FindDrugId();
		$physicianid = $this->FindPhysicianId();
		$clinicid = $this->FindClinicId();
		$drugid = $this->FindDrugId();
		
		$query = "SELECT `PrescriptionId` FROM `Prescriptions` WHERE `DispensesAllowed` = :dispensesallowed AND `DrugId` = :drugid AND `PhysicianId` = :physicianid AND `ClinicId` = :clinicid AND `Date` = :date AND `PatientId` = :patientid;";
        $stmt_find_PrescriptionId = $this->con->prepare($query);
        $stmt_find_PrescriptionId->bindParam(":dispensesallowed", $this->dispenses_allowed);
        $stmt_find_PrescriptionId->bindParam(":drugid", $drugid);
		$stmt_find_PrescriptionId->bindParam(":physicianid", $physicianid);
		$stmt_find_PrescriptionId->bindParam(":clinicid", $clinicid);
		$stmt_find_PrescriptionId->bindParam(":date", $this->prescription_date);
		$stmt_find_PrescriptionId->bindParam(":patientid", $patientid);
        $stmt_find_PrescriptionId->execute();
        $prescriptionid = $stmt_find_PrescriptionId->fetch()[0];
		
		$query = "INSERT INTO `MedicationDispensed` (`Date`, `PrescriptionId`) VALUES (:date, :prescriptionid);";
		$stmt_dispense = $this->con->prepare($query);
		$stmt_dispense->bindParam(":date", $dispensed_date);
		$stmt_dispense->bindParam(":prescriptionid",$prescriptionid);
		$stmt_dispense->execute();
		
	}
	private function FindDrugId()
	{
		//These queries find a drug id for the drug that is being prescribed using generic name as a specifier
		
		$query = "SELECT `DrugNameId` from `DrugNames` WHERE (`GenericName`) = (:genericname);";
		$stmt_search1 = $this->con->prepare($query);
		$stmt_search1->bindParam(":genericname", $this->drug->generic_name);
		$stmt_search1->execute();
		$drugnameid = $stmt_search1->fetch()[0];
		
		$query = "SELECT `DrugId` from `Drugs` WHERE (`DrugNameId`) = (:drugnameid);";
		$stmt_search2 = $this->con->prepare($query);
		$stmt_search2->bindParam(":drugnameid", $drugnameid);
		$stmt_search2->execute();
		$drugid = $stmt_search2->fetch()[0];
		
		return $drugid;
	}
	
	private function FindPhysicianId()
	{
			// This queries the database for the primary key of the physician based on the user typing in first name, last name, and email which should be specific to a physician
        $query = "SELECT `PhysicianId` FROM `Physicians` WHERE `FirstName` = :firstname AND `LastName` = :lastname AND `Email` = :email;";
        $stmt_find_PhysicianId = $this->con->prepare($query);
        $stmt_find_PhysicianId->bindParam(":firstname", $this->physician->first_name);
        $stmt_find_PhysicianId->bindParam(":lastname", $this->physician->last_name);
        $stmt_find_PhysicianId->bindParam(":email", $this->physician->email);
        $stmt_find_PhysicianId->execute();
        $physicianid = $stmt_find_PhysicianId->fetch()[0];
		
		return $physicianid;
	}
	private function FindClinicId()
	{
		// This queries ClinicId from the clinics object using the name as identifier
		
		$query = "SELECT `ClinicId` FROM `Clinics` WHERE `Name` = :name AND `Address` = :address;";
        $stmt_find_ClinicId = $this->con->prepare($query);
        $stmt_find_ClinicId->bindParam(":name", $this->clinic->name);
        $stmt_find_ClinicId->bindParam(":address", $this->clinic->address);
        $stmt_find_ClinicId->execute();
        $clinicid = $stmt_find_ClinicId->fetch()[0];
		
		return $clinicid;
	}
	private function FindPatientId()
	{
		// This queries PatientId from the old database using Patient first name, last name, and DOB as an identifier
		
		$query = "SELECT `PatientId` FROM `Patient` WHERE `fname` = :fname AND `lname` = :lname AND `dob` = :dob;";
        $stmt_find_PatientId = $this->con->prepare($query);
        $stmt_find_PatientId->bindParam(":fname", $this->fname);
		$stmt_find_PatientId->bindParam(":lname", $this->lname);
		$stmt_find_PatientId->bindParam(":dob", $this->dob);
        $stmt_find_PatientId->execute();
        $patientid = $stmt_find_PatientId->fetch()[0];
		
		return $patientid;
	}
	
}

try {
    $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$drug = new Drug("levitirecetam", "Keppra", "Intravenous",200, "mg", $con);
$patient = new Patient("Steve", "Layfield", "04-02-1992", "1420 10th Street South", "7062884070", "sjlayfield@gmail.com", $con);
$clinic = new Clinic("HappyLand", "100 Optimist Ave", "Joyville", "AL", "00000", "www.lol.com", "123-456-7890", "hello@gmail.com", $con);
$physician = new Physician("Matthew", "Charles", "Hess", "", "706-288-4070", "matthess@uab.edu", "Orthopedic Surgery", $con);
$prescription = new Prescription($drug, $physician, "2-6-2017", "2-6-2017", 3, $patient, $clinic, $con);
echo $prescription->drug;
echo $prescription->physician ."\n";
echo $prescription->prescription_date ."\n";
echo $prescription->dispensed_date ."\n";
echo $prescription->dispenses_allowed ."\n";
echo $prescription->patient ."\n";
echo $prescription->clinic ."\n";
$prescription->storePrescription();
?>