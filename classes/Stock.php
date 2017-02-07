<?php

require("../includes/db.php");

spl_autoload_register(function ($name) {
    require($name . ".php");
});
class Stock
{ 
	public $drug;
	public $amount;
	public $cost;
	public $minimum_needed;

	public function __construct(Drug $drug,$amount,$cost,$minimum_needed,$con)
	{
		$this->drug = $drug;
		$this->amount = $amount;
        $this->cost = $cost;
        $this->minimum_needed = $minimum_needed;
		
		$this->con = $con;
	}
	
	public function storeStock()
	{
	// query for primary key of selected drug units
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
		
		$query = "SELECT `StockId` from `Stock` WHERE (`DrugId`) = (:drugid);";
		$stmt_search3 = $this->con->prepare($query);
		$stmt_search3->bindParam(":drugid", $drugid);
		$stmt_search3->execute();
		$stockid = $stmt_search3->fetch()[0];
		
        // Unit does not exist --> insert and pull primary key
        if (empty($stockid)) {
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
			
			$query = "INSERT INTO `Stock` (`Amount`, `Cost`, `MinimumNeeded`, `DrugId`) VALUES (:amount, :cost, :minimumneeded, :drugid);";
			$stmt_storestock = $this->con->prepare($query);
			$stmt_storestock->bindParam(":amount", $this->amount);
			$stmt_storestock->bindParam(":cost", $this->cost);
			$stmt_storestock->bindParam(":minimumneeded", $this->minimum_needed);
			$stmt_storestock->bindParam(":drugid",$drugid);
			$stmt_storestock->execute();
		}
	}
	public function changeCost($cost)
    {
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
		
		$query = "UPDATE `Stock` SET `Cost` = (:cost) WHERE `DrugId` = (:drugid);";
		$stmt_cost = $this->con->prepare($query);
		$stmt_cost->bindParam(":cost",$cost);
		$stmt_cost->bindParam(":drugid",$drugid);
		$stmt_cost->execute();
		
		$this->cost = $cost;
    }
    public function changeAmount($amount)
    {
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
		
		$query = "UPDATE `Stock` SET `Amount` = (:amount) WHERE `DrugId` = (:drugid);";
		$stmt_amount = $this->con->prepare($query);
		$stmt_amount->bindParam(":amount",$amount);
		$stmt_amount->bindParam(":drugid",$drugid);
		$stmt_amount->execute();
		
		$this->amount = $amount;
    }
    public function changeMinimumNeeded($minimum_needed)
    {
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
		
		$query = "UPDATE `Stock` SET `MinimumNeeded` = (:minimum_needed) WHERE `DrugId` = (:drugid);";
		$stmt_minimum_needed = $this->con->prepare($query);
		$stmt_minimum_needed->bindParam(":minimum_needed",$minimum_needed);
		$stmt_minimum_needed->bindParam(":drugid",$drugid);
		$stmt_minimum_needed->execute();
		
		$this->minimum_needed = $minimum_needed;
    }
}


try {
    $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$drug = new Drug("levitirecetam", "Keppra", "Intravenous",200, "mg", $con);
$drug->storeDrug();
$stock = new Stock($drug,30,60,90, $con);
$stock->storeStock();
//$stock->changeCost(100);
//$stock->changeAmount(100);
//$stock->changeMinimumNeeded(100);
?>