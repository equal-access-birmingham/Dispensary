<?php
class LabOrder
{
    public $lab;
    public $patient;
    public $physician;
    public $date_ordered;
    public $date_results;
    public $value;
    public $unit;

    /**
     * @var PDO $con Database connection
     */
    private $con;

    public function __construct(Lab $lab, Patient $patient, Physician $physician, $date_ordered, PDO $con)
    {
        // Information of lab being ordered
        $this->lab = $lab;
        $this->patient = $patient;
        $this->physician = $physician;
        $this->date_ordered = $date_ordered;

        // Database connection
        $this->con = $con;
    }

    /**
     * Checks to see if a lab order is already in the database
     * @return boolean
     */
    public function labOrderInDatabase()
    {
        // Get IDs from objects for query
        $patient_id = $this->patient->getId();
        $physician_id = $this->physician->getId();
        $lab_test_id = $this->lab->getLabTestId();

        // Query for duplicate lab orders
        $query = "SELECT COUNT(*) FROM `PatientLabTests` 
                    INNER JOIN `LabComponentsAssociation` ON `PatientLabTests`.`LabComponentAssociationId` = `LabComponentsAssociation`.`LabComponentAssociationId`
                    WHERE `PatientLabTests`.`PatientId` = :PatientId AND `PatientLabTests`.`PhysicianId` = :PhysicianId AND `PatientLabTests`.`DateOrdered` AND `LabComponentsAssociation`.`LabTestId` = :LabTest;";
        $stmt_check_duplicate = $this->con->prepare();
        $stmt_check_duplicate->bindParam(":PatientId", $patient_id);
        $stmt_check_duplicate->bindParam(":PhysicianId", $physician_id);
        $stmt_check_duplicate->bindParam(":PatientId", $this->date_ordered);
        $stmt_check_duplicate->bindParam(":PatientId", $lab_test_id);
        $stmt_check_duplicate->execute();
        $lab_order_count = $stmt_check_duplicate->fetch()[0];

        // Lab order already in database
        if ($lab_order_count > 0) {
            return true;
        }

        // lab order not in database
        return false;
    }

    /**
     * Retrieves all the lab order IDs for a single lab order (this is due to the lab orders being stored as
     *     individual components)
     * @return array
     */
    public function getLabOrderIds()
    {
        // Lab order not in the database and IDs can't be retrieved
        if (! $this->labOrderInDatabase()) {
            throw new Exception("This lab order has not been stored in the database yet");
        }

        // Get IDs from objects for query
        $patient_id = $this->patient->getId();
        $physician_id = $this->physician->getId();
        $lab_test_id = $this->lab->getLabTestId();

        // Query to grab lab order IDs from the `PatientLabTests` table
        $query = "SELECT `PatientLabTests`.`PatientLabTestId` FROM `PatientLabTests` 
                    INNER JOIN `LabComponentsAssociation` ON `PatientLabTests`.`LabComponentAssociationId` = `LabComponentsAssociation`.`LabComponentAssociationId`
                    WHERE `PatientLabTests`.`PatientId` = :PatientId AND `PatientLabTests`.`PhysicianId` = :PhysicianId AND `PatientLabTests`.`DateOrdered` AND `LabComponentsAssociation`.`LabTestId` = :LabTest;";
        $stmt_lab_order_id = $this->con->prepare($query);
        $stmt_lab_order_id->bindParam(":PatientId", $patient_id);
        $stmt_lab_order_id->bindParam(":PhysicianId", $physician_id);
        $stmt_lab_order_id->bindParam(":PatientId", $this->date_ordered);
        $stmt_lab_order_id->bindParam(":PatientId", $lab_test_id);
        $stmt_lab_order_id->execute();
        return $stmt_lab_order_id->fetchAll();
    }

    /**
     * Creates a lab order with an order date but no finalized values (date_results, value)
     * @return void
     */
    public function createLabOrder()
    {
        // lab order is already in the database and shouldn't be duplicated
        if ($this->labOrderInDatabase()) {
            throw new Exception("This lab order is already in the database");
        }

        // Query to insert lab order into database
        $query = "INSERT INTO `PatientLabTests` (`LabComponentAssociationId`, `PhysicianId`, `DateOrdered`, `PatientId`) VALUES (:LabComponentAssociationId, :PhysicianId, :DateOrdered, :PatientId);";
        $stmt_create_lab = $this->con->prepare($query);
        $stmt_create_lab->bindParam(":LabComponentAssociationId", $lab_component_association_id);
        $stmt_create_lab->bindParam(":PhysicianId", $physician_id);
        $stmt_create_lab->bindParam(":DateOrdered", $this->date_ordered);
        $stmt_create_lab->bindParam(":PatientId", $patient_id);

        // both of these are theoretically untested 
        $physician_id = $this->physician->getId();
        $patient_id = $this->patient->getPatientId();

        // using the untested function getLabAssociationIds
        // goes through each of the lab components and adds their association to the LabOrder table
        foreach ($this->lab->getLabAssociationIds() as $lab_component_association_id) {
            $stmt_create_lab->execute();
        }
    }

    /**
     * Delete a lab order
     * @return void
     */
    public function deleteLabOrder()
    {
        // Lab order not in database and can't be deleted
        if (! $this->labOrderInDatabase()) {
            throw new Exception("This lab order is not in the database and can't be deleted");
        }

        // Query to delete lab orders form the 
        $query = "DELETE FROM `PatientLabTests` WHERE `PatientLabTestId = :PatientLabTestId;";
        $stmt_delete_lab_order = $this->con->prepare($query);
        $stmt_delete_lab_order->bindParam(":PatientLabTestId", $patient_lab_test_id);

        // Delete the lab order
        foreach ($this->getLabOrderIds() as $patient_lab_test_id) {
            $stmt_delete_lab_order->execute();
        }
    }

    public function addResults($value, $unit, $date)
    {

    }

    public function changePhysician()
    {

    }

    public function changePatient()
    {
        
    }

    public function changeValue()
    {
        
    }

    public function changeDateOrdered()
    {

    }

    public function changeDateResults()
    {

    }

    public function changeUnits()
    {

    }
}