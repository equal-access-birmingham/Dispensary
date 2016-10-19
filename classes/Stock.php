<?php
require_once("../includes/db.php");
class Stock
{
	public $drug;
	public $amount;
	public $cost;
	public $minimum_needed;

	public function __construct(Drug $drug,$amount,$cost,$minimum_needed,$con)
	{
		$this->$drug = $drug;
		$this->amount = $amount;
        $this->cost = $cost;
        $this->minimum_needed = $minimum_needed;
		
		$this->con = con;
	}
	
	public function storeStock()
	{
		$query = "SELECT `DrugNameId` from `DrugNames` WHERE (`GenericName`) = (:genericname);";
        $stmt_search1 = $this->con->prepare($query);
        $stmt_search1->bindParam(":genericname", $this->$drug->generic_name);
        $stmt_search1->execute();
        $drugnameid = $stmt_search1->fetch()[0];
		
		$query = "SELECT `DrugId` from `Drugs` WHERE (`DrugNameId`) = (:drugnameid);";
        $stmt_search2 = $this->con->prepare($query);
        $stmt_search2->bindParam(":drugnameid", $drugnameid);
        $stmt_search2->execute();
        $drugid = $stmt_search2->fetch()[0];
		
		$query = "INSERT INTO `Stock` (`Amount`, `Cost`, `MinimumNeeded`, `DrugId`) VALUES (:amount, :cost, :minimumneeded, :drugid);";
        $stmt_storestock = $this->con->prepare($query);
        $stmt_storestock->bindParam(":amount", $this->$amount);
        $stmt_storestock->bindParam(":cost", $this->$cost);
		$stmt_storestock->bindParam(":minimumneeded", $this->$minimumneeded);
		$stmt_storestock->bindParam(":drugid",$drugid);
        $stmt_storestock->execute();
	}
	public function changeCost($cost)
    {
		$query = "SELECT `DrugNameId` from `DrugNames` WHERE (`GenericName`) = (:genericname);";
        $stmt_search1 = $this->con->prepare($query);
        $stmt_search1->bindParam(":genericname", $this->$drug->generic_name);
        $stmt_search1->execute();
        $drugnameid = $stmt_search1->fetch()[0];
		
		$query = "SELECT `DrugId` from `Drugs` WHERE (`DrugNameId`) = (:drugnameid);";
        $stmt_search2 = $this->con->prepare($query);
        $stmt_search2->bindParam(":drugnameid", $drugnameid);
        $stmt_search2->execute();
        $drugid = $stmt_search2->fetch()[0];
		
		$query = "UPDATE `Stock` SET `Cost` = (:cost) WHERE `DrugId` = (:drugid);";
		$stmt_editcost = $this->con->prepare($query)
		$stmt_editcost->bind_param(":cost",$cost);
		$stmt_editcost->bind_param(":drugid",$drugid);
		$stmt_editcost->execute();
		
		$this->cost = $cost;
    }
    public function changeAmount()
    {
		$query = "SELECT `DrugNameId` from `DrugNames` WHERE (`GenericName`) = (:genericname);";
        $stmt_search1 = $this->con->prepare($query);
        $stmt_search1->bindParam(":genericname", $this->$drug->generic_name);
        $stmt_search1->execute();
        $drugnameid = $stmt_search1->fetch()[0];
		
		$query = "SELECT `DrugId` from `Drugs` WHERE (`DrugNameId`) = (:drugnameid);";
        $stmt_search2 = $this->con->prepare($query);
        $stmt_search2->bindParam(":drugnameid", $drugnameid);
        $stmt_search2->execute();
        $drugid = $stmt_search2->fetch()[0];
		
		$query = "UPDATE `Stock` SET `Amount` = (:amount) WHERE `DrugNameId` = (:drugid);";
		$stmt_editcost = $this->con->prepare($query)
		$stmt_editcost->bind_param(":amount",$amount);
		$stmt_editcost->bind_param(":drugid",$drugid);
		$stmt_editcost->execute();
		
		$this->amount = $amount;
    }
    public function changeMinimumNeeded()
    {
		$query = "SELECT `DrugNameId` from `DrugNames` WHERE (`GenericName`) = (:genericname);";
        $stmt_search1 = $this->con->prepare($query);
        $stmt_search1->bindParam(":genericname", $this->$drug->generic_name);
        $stmt_search1->execute();
        $drugnameid = $stmt_search1->fetch()[0];
		
		$query = "SELECT `DrugId` from `Drugs` WHERE (`DrugNameId`) = (:drugnameid);";
        $stmt_search2 = $this->con->prepare($query);
        $stmt_search2->bindParam(":drugnameid", $drugnameid);
        $stmt_search2->execute();
        $drugid = $stmt_search2->fetch()[0];
		
		$query = "UPDATE `Stock` SET `MinimumNeeded` = (:minimumneeded) WHERE `DrugNameId` = (:drugid);";
		$stmt_editcost = $this->con->prepare($query)
		$stmt_editcost->bind_param(":minimumneeded",$minimum_needed);
		$stmt_editcost->bind_param(":drugid",$drugid);
		$stmt_editcost->execute();
		
		$this->minimum_needed = $minimum_needed;
    }
}


try {
    $con = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$drug = new Stock(20,50.00,5, $con);
echo $stock->$amount . "\n";
echo $stock->$cost . "\n";
echo $stock->$minimum_needed . "\n";
$stock->storeStock();
?>