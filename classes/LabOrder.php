<?php
/**
 * LabOrder class that allows a Lab (lab panel to be more precise) to be ordered by a physician
 *     for a patient
 * 
 * Public Method List
 *  - labTestInDatabase
 *  - labOrderInDatabase
 *  - getLabTestId
 *  - getLabOrderIds
 *  - createLabOrder
 *  - deleteLabOrder
 *  - addResult
 *  - changePhysician
 *  - changePatient
 *  - changeDateOrdered
 *  - getDateResults
 *  - changeDateResults
 *  - getValue
 *  - changeValue
 *  - getUnits
 *  - changeUnits

 * (Finished NOT tested)
 */
class LabOrder
{
    /**
     * @var Lab $lab The lab object associated with the lab order
     */
    public $lab;

    /**
     * @var Patient $patient The patient object the lab order is for
     */
    public $patient;

    /**
     * @var Physician $physician The physician object doing the ordering
     */
    public $physician;

    /**
     * @var string $date_ordered The date the lab was/is ordered
     */
    public $date_ordered;

    // public $date_results;
    // public $value;
    // public $unit;

    /**
     * @var PDO $con Database connection
     */
    private $con;

    /**
     * Creates the initial lab order
     * @param Lab $lab The lab that is being ordered
     * @param Patient $patient The patient that the lab is being ordered for
     * @param Physician $physician The physician that is ordering the lab for the patient
     * @param string $date_ordered The date that the lab was ordered
     * @param PDO $con The database connection for storing the labs
     */
    public function __construct(Lab $lab, Patient $patient, Physician $physician, $date_ordered, PDO $con)
    {
        // Check for valid date
        if (! preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date)) {
            throw new Exception("The date must be in the format YYYY-MM-DD");
        }

        // Information of lab being ordered
        $this->lab = $lab;
        $this->patient = $patient;
        $this->physician = $physician;
        $this->date_ordered = $date_ordered;

        // Database connection
        $this->con = $con;
    }

    /**
     * Checks to see if a specific lab test for a patient (lab test and lab component) is in the database 
     * @param LabComponent $lab_component Object storing the information on the lab component to look for
     * @return boolean
     */
    public function labTestInDatabase(LabComponent $lab_component)
    {
        // Get IDs from objects for query
        $patient_id = $this->patient->getId();
        $physician_id = $this->physician->getId();
        $lab_test_id = $this->lab->getLabTestId();
        $lab_component_id = $lab_component->getId();

        // Query to check if lab test is already in the database
        $query = "SELECT COUNT(*) FROM `PatientLabTests` 
                    INNER JOIN `LabComponentsAssociation` ON `PatientLabTests`.`LabComponentAssociationId` = `LabComponentsAssociation`.`LabComponentAssociationId`
                    WHERE `PatientLabTests`.`PatientId` = :PatientId AND `PatientLabTests`.`PhysicianId` = :PhysicianId AND `PatientLabTests`.`DateOrdered` = :DateOrdered AND `LabComponentsAssociation`.`LabTestId` = :LabTestId AND `LabComponentAssociation`.`LabTestComponentId` = :LabTestComponentId;";
        $stmt_check_duplicate = $this->con->prepare($query);
        $stmt_check_duplicate->bindParam(":PatientId", $patient_id);
        $stmt_check_duplicate->bindParam(":PhysicianId", $physician_id);
        $stmt_check_duplicate->bindParam(":DateOrdered", $this->date_ordered);
        $stmt_check_duplicate->bindParam(":LabTestId", $lab_test_id);
        $stmt_check_duplicate->bindParam(":LabTestComponentId", $lab_component_id);
        $stmt_check_duplicate->execute();
        $lab_test_count = $stmt_check_duplicate->fetch()[0];

        // Lab test already in database
        if ($lab_test_count > 0) {
            return true;
        }

        // lab orer not in database
        return false;
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
                    WHERE `PatientLabTests`.`PatientId` = :PatientId AND `PatientLabTests`.`PhysicianId` = :PhysicianId AND `PatientLabTests`.`DateOrdered` = :DateOrdered AND `LabComponentsAssociation`.`LabTestId` = :LabTestId;";
        $stmt_check_duplicate = $this->con->prepare($query);
        $stmt_check_duplicate->bindParam(":PatientId", $patient_id);
        $stmt_check_duplicate->bindParam(":PhysicianId", $physician_id);
        $stmt_check_duplicate->bindParam(":DateOrdered", $this->date_ordered);
        $stmt_check_duplicate->bindParam(":LabTestId", $lab_test_id);
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
     * Retrieves the ID for a specific lab test ordered on a patient from the `PatientLabTests` table
     * @param LabComponent $lab_component
     * @return int
     */
    public function getLabTestId(LabComponent $lab_component)
    {
        if (! $this->labTestInDatabase($lab_component)) {
            throw new Exception("The lab test is not in the database and the ID can't be retrieved");
        }

        $query = "SELECT `PatientLabTests`.`PatientLabTestId` FROM `PatientLabTests` 
                    INNER JOIN `LabComponentsAssociation` ON `PatientLabTests`.`LabComponentAssociationId` = `LabComponentsAssociation`.`LabComponentAssociationId`
                    WHERE `PatientLabTests`.`PatientId` = :PatientId AND `PatientLabTests`.`PhysicianId` = :PhysicianId AND `PatientLabTests`.`DateOrdered` = :DateOrdered AND `LabComponentsAssociation`.`LabTestId` = :LabTestId AND `LabComponentsAssociation`.`LabTestComponentId` = :LabTestComponentId;";
        $stmt_lab_test_id = $this->con->prepare($qurey);
        $stmt_lab_test_id->bindParam(":PatientId", $patient_id);
        $stmt_lab_test_id->bindParam(":PhysicianId", $physician_id);
        $stmt_lab_test_id->bindParam(":DateOrdered", $this->date_ordered);
        $stmt_lab_test_id->bindParam(":LabTestId", $lab_test_id);
        $stmt_lab_test_id->bindParam(":LabTestComponentId", $lab_component_id);
        $stmt_lab_test_id->execute();
        return $stmt_lab_test_id->fetch()[0];
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
                    WHERE `PatientLabTests`.`PatientId` = :PatientId AND `PatientLabTests`.`PhysicianId` = :PhysicianId AND `PatientLabTests`.`DateOrdered` = :DateOrdered AND `LabComponentsAssociation`.`LabTestId` = :LabTestId;";
        $stmt_lab_order_id = $this->con->prepare($query);
        $stmt_lab_order_id->bindParam(":PatientId", $patient_id);
        $stmt_lab_order_id->bindParam(":PhysicianId", $physician_id);
        $stmt_lab_order_id->bindParam(":DateOrdered", $this->date_ordered);
        $stmt_lab_order_id->bindParam(":LabTestId", $lab_test_id);
        $stmt_lab_order_id->execute();
        return $stmt_lab_order_id->fetchAll();
    }

    /**
     * Helper function that finds the database primary key for a provided unit measure and inserts it if not found
     * @param string $unit The unit measure to find the primary key for
     * @return int
     */
    private function findUnitId($unit)
    {
        // query for primary key of selected drug units
        $query = "SELECT `UnitId` FROM `Units` WHERE `Unit` = :Unit;";
        $stmt_find_unit_id = $this->con->prepare($query);
        $stmt_find_unit_id->bindParam(":Unit", $unit);
        $stmt_find_unit_id->execute();
        $unit_id = $stmt_find_unit_id->fetch()[0];

        // Unit does not exist --> insert and pull primary key
        if (empty($unit_id)) {
            // insert new unit into unit table
            $query = "INSERT INTO `Units` (`Unit`) VALUES (:Unit);";
            $stmt_insert_unit = $this->con->prepare($query);
            $stmt_insert_unit->bindParam(":Unit", $unit);
            $stmt_insert_unit->execute();

            // Rerun query to grab primary key of drug unit
            $stmt_find_unit_id->execute();
            $unit_id = $stmt_find_unit_id->fetch()[0];
        }

        return $unit_id;
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

    /**
     * Adds a result to a patient's lab test.  Must be added individually due to database setup
     * @param LabComponent $lab_component The lab component of the lab test to add the result to
     * @param float $value The value of the lab result
     * @param string $unit The units associated with the value
     * @param string $date The date that the result came in
     * @return void
     */
    public function addResult(LabComponent $lab_component, $value, $unit, $date)
    {
        // Clean the inputs
        if (! is_numeric($value)) {
            throw new Exception("The value of the result must be a number");
        }

        if (! is_string($unit)) {
            throw new Exception("The units of the result must be a string");
        }

        if (! preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date)) {
            throw new Exception("The date must be in the format YYYY-MM-DD");
        }

        // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            $this->createLabOrder();
        }

        // lab test for the given patient is not in the database, which is a serious problem
        if (! $this->labTestInDatabase($lab_component)) {
            throw new Exception("This lab test has not been ordered for the patient");
        }

        // lab test id for the current component to have the results updated
        $lab_test_id = $this->getLabTestId($lab_component);
        $unit_id = $this->findUnitId($unit);

        // Query to add results to database through update
        $query = "UPDATE `PatientLabTests` SET `Value` = :Value, `UnitId` = :UnitId, `DateResults` = :DateResults WHERE `PatientLabTestId` = :PatientLabTestId;";
        $stmt_add_result = $this->con->prepare($query);
        $stmt_add_result->bindParam(":Value", $value);
        $stmt_add_result->bindParam(":UnitId", $unit_id);
        $stmt_add_result->bindParam(":DateResults", $date);
        $stmt_add_result->bindParam(":PatientLabTestId", $lab_test_id);
        $stmt_add_result->execute();

        // Add values to object
        $this->date_results = $date;
    }

    /**
     * Changes the orderding physician on the lab orders for a given patient
     * @param Physician $physician The new physician to put on the order
     */
    public function changePhysician(Physician $physician)
    {
        // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            $this->createLabOrder();
        }

        // Acquire IDs for query
        $patient_id = $this->patient->getId();
        $physician_id = $this->physician->getId();
        $lab_test_id = $this->lab->getLabTestId();
        $new_physician_id = $physician->getId();

        // Query to change ordering physician through update
        $query = "UPDATE `PatientLabTests` SET `PatientLabTests`.`PhysicianId` = :NewPhysicianId
                    INNER JOIN `LabComponentsAssocation` ON `PatientLabTests`.`LabComponentAssociationId` = `LabComponentsAssociation`.`LabComponentAssociationId`
                    WHERE `PatientLabTests`.`PatientId` = :PatientId AND `PatientLabTests`.`PhysicianId` = :PhysicianId AND `LabComponentsAssociation`.`LabTestId` = :LabTestId AND `PatientLabTests`.`DateOrdered` = :DateOrdered;";
        $stmt_change_physician = $this->con->prepare($query);
        $stmt_change_physician->bindParam(":NewPhysicianId", $new_physician_id);
        $stmt_change_physician->bindParam(":PatientId", $patient_id);
        $stmt_change_physician->bindParam(":PhysicianId", $physician_id);
        $stmt_change_physician->bindParam(":LabTestId", $lab_test_id);
        $stmt_change_physician->bindParam(":DateOrdered", $this->date_ordered);
        $stmt_change_physician->execute();

        // Update object property
        $this->physician = $physician;
    }

    /**
     * Changes the patient that the lab was ordered for
     * @param Patient $patient The new patient to put on the order
     * @return void
     */
    public function changePatient(Patient $patient)
    {
       // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            $this->createLabOrder();
        }

        // Acquire IDs for query
        $patient_id = $this->patient->getId();
        $physician_id = $this->physician->getId();
        $lab_test_id = $this->lab->getLabTestId();
        $new_patient_id = $patient->getId();

        // Query to change ordering physician through update
        $query = "UPDATE `PatientLabTests` SET `PatientLabTests`.`PatientId` = :NewPatientId
                    INNER JOIN `LabComponentsAssocation` ON `PatientLabTests`.`LabComponentAssociationId` = `LabComponentsAssociation`.`LabComponentAssociationId`
                    WHERE `PatientLabTests`.`PatientId` = :PatientId AND `PatientLabTests`.`PhysicianId` = :PhysicianId AND `LabComponentsAssociation`.`LabTestId` = :LabTestId AND `PatientLabTests`.`DateOrdered` = :DateOrdered;";
        $stmt_change_patient = $this->con->prepare($query);
        $stmt_change_patient->bindParam(":NewPatientId", $new_patient_id);
        $stmt_change_patient->bindParam(":PatientId", $patient_id);
        $stmt_change_patient->bindParam(":PhysicianId", $physician_id);
        $stmt_change_patient->bindParam(":LabTestId", $lab_test_id);
        $stmt_change_patient->bindParam(":DateOrdered", $this->date_ordered);
        $stmt_change_patient->execute();

        // Update object property
        $this->patient = $patient;
    }

    /**
     * Changes the order date of the lab order
     * @param string $date The date to change the lab order to
     * @return void
     */
    public function changeDateOrdered($date)
    {
        // Check for valid date
        if (! preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date)) {
            throw new Exception("The date must be in the format YYYY-MM-DD");
        }

        // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            $this->createLabOrder();
        }

        // lab test for the given patient is not in the database, which is a serious problem
        if (! $this->labTestInDatabase($lab_component)) {
            throw new Exception("This lab test has not been ordered for the patient");
        }

        // Acquire IDs for query
        $patient_id = $this->patient->getId();
        $physician_id = $this->physician->getId();
        $lab_test_id = $this->lab->getLabTestId();

        // Query to change ordering physician through update
        $query = "UPDATE `PatientLabTests` SET `PatientLabTests`.`DateOrdered` = :NewDateOrdered
                    INNER JOIN `LabComponentsAssocation` ON `PatientLabTests`.`LabComponentAssociationId` = `LabComponentsAssociation`.`LabComponentAssociationId`
                    WHERE `PatientLabTests`.`PatientId` = :PatientId AND `PatientLabTests`.`PhysicianId` = :PhysicianId AND `LabComponentsAssociation`.`LabTestId` = :LabTestId AND `PatientLabTests`.`DateOrdered` = :DateOrdered;";
        $stmt_change_patient = $this->con->prepare($query);
        $stmt_change_patient->bindParam(":NewDateOrdered", $date);
        $stmt_change_patient->bindParam(":PatientId", $patient_id);
        $stmt_change_patient->bindParam(":PhysicianId", $physician_id);
        $stmt_change_patient->bindParam(":LabTestId", $lab_test_id);
        $stmt_change_patient->bindParam(":DateOrdered", $this->date_ordered);
        $stmt_change_patient->execute();

        // Update object property
        $this->date_ordered = $date;
    }

    /**
     * Acquires the result dates of a single lab component from a lab order
     * @param LabComponent $lab_component The lab component of the lab order to retrieve the result date from
     * @return void
     */
    public function getDateResults(LabComponent $lab_component)
    {
        // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            throw new Exception("The lab hasn't been stored yet, and won't have a value");
        }

        // lab test for the given patient is not in the database, which is a serious problem
        if (! $this->labTestInDatabase($lab_component)) {
            throw new Exception("This lab test has not been ordered for the patient");
        }

        // Get the lab test id to pull the value
        $lab_test_id = $this->getLabTestId($lab_component);

        $query = "SELECT `DateResults` FROM `PatientLabTests` WHERE `PatientLabTestId` = :PatientLabTestId;";
        $stmt_get_result_date = $this->con->prepare($query);
        $stmt_get_result_date->bindParam(":PatientLabTestId", $lab_test_id);
        $stmt_get_result_date->execute();
        return $stmt_get_result_date->fetch()[0] ? : "N/A";
    }

    /**
     * Updates a single result date for a lab component of a lab order
     * @param LabComponent $lab_component The lab component of the lab order to update the result date for
     * @param string $date The date to update the result date of the lab component to
     * @return void
     */
    public function changeDateResults(LabComponent $lab_component, $date)
    {
        // Check for valid date
        if (! preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date)) {
            throw new Exception("The date must be in the format YYYY-MM-DD");
        }

        // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            $this->createLabOrder();
        }

        // lab test for the given patient is not in the database, which is a serious problem
        if (! $this->labTestInDatabase($lab_component)) {
            throw new Exception("This lab test has not been ordered for the patient");
        }

        // Acquire IDs for query
        $lab_test_id = $this->getLabTestId();

        // Query to change the date of the results for the lab order
        $query = "UPDATE `PatientLabTests` SET `DateResults` = :NewDateResults WHERE `PatientLabTestId` = :PatientLabTestId;";
        $stmt_change_patient = $this->con->prepare($query);
        $stmt_change_patient->bindParam(":NewDateResults", $date);
        $stmt_change_patient->bindParam(":LabTestId", $lab_test_id);
        $stmt_change_patient->execute();
    }

    /**
     * Retrieves the value of a specific lab component for a lab order (returns "N/A" if no value exists)
     * @param LabComponent $lab_component The lab component of the lab order to retrieve the value of
     * @return string
     */
    public function getValue(LabComponent $lab_component)
    {
        // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            throw new Exception("The lab hasn't been stored yet, and won't have a value");
        }

        // lab test for the given patient is not in the database, which is a serious problem
        if (! $this->labTestInDatabase($lab_component)) {
            throw new Exception("This lab test has not been ordered for the patient");
        }

        // Get the lab test id to pull the value
        $lab_test_id = $this->getLabTestId($lab_component);

        $query = "SELECT `Value` FROM `PatientLabTests` WHERE `PatientLabTestId` = :PatientLabTestId;";
        $stmt_get_value = $this->con->prepare($query);
        $stmt_get_value->bindParam(":PatientLabTestId", $lab_test_id);
        $stmt_get_value->execute();
        return $stmt_get_value->fetch()[0] ? : "N/A";
    }

    /**
     * Changes the value of a specific lab component in the lab order
     * @param LabComponent $lab_component The lab component of the lab order to change the value of
     * @param float $value The value to change the lab component to
     * @return void
     */
    public function changeValue(LabComponent $lab_component, $value)
    {
        // clean input
        if (! is_numeric($value)) {
            throw new Exception("The value of the lab component must be a number");
        }

        // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            $this->createLabOrder();
        }

        // lab test for the given patient is not in the database, which is a serious problem
        if (! $this->labTestInDatabase($lab_component)) {
            throw new Exception("This lab test has not been ordered for the patient");
        }

        // lab test id for the current component to have the results updated
        $lab_test_id = $this->getLabTestId($lab_component);

        $query = "UPDATE `PatientLabTests` SET `Value` = :Value WHERE `PatientLabTestId` = :PatientLabTestId;";
        $stmt_change_value = $this->con->prepare($query);
        $stmt_change_value->bindParam(":Value", $value);
        $stmt_change_value->bindParam(":PatientLabTestId", $lab_test_id);
        $stmt_change_value->execute();
    }

    /**
     * Retrieves the human-readable unit for a lab component of a lab order (returns "N/A" if none exists)
     * @param LabComponent $lab_component The lab component of the lab test to get the unit for
     * @return string
     */
    public function getUnits(LabComponent $lab_component)
    {
        // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            throw new Exception("The lab hasn't been stored yet, and won't have a unit");
        }

        // lab test for the given patient is not in the database, which is a serious problem
        if (! $this->labTestInDatabase($lab_component)) {
            throw new Exception("This lab test has not been ordered for the patient");
        }

        // Get the lab test id to pull the value
        $lab_test_id = $this->getLabTestId($lab_component);

        $query = "SELECT `Units`.`Unit` FROM `PatientLabTests` 
                    INNER JOIN `Units` ON `PatientLabTests`.`UnitId` = `Units`.`UnitId`
                    WHERE `PatientLabTestId` = :PatientLabTestId;";
        $stmt_get_unit = $this->con->prepare($query);
        $stmt_get_unit->bindParam(":PatientLabTestId", $lab_test_id);
        $stmt_get_unit->execute();
        return $stmt_get_unit->fetch()[0] ? : "N/A";
    }

    /**
     * Changes the units on a specific lab component of a lab order
     * @param LabComponent $lab_component The lab component to change the units for
     * @param string $unit The units to change the lab component too
     * @return void
     */
    public function changeUnits(LabComponent $lab_component, $unit)
    {
        // Clean the input
        if (! is_string($unit)) {
            throw new Exception("The units must be a string");
        }

        // Lab order not in the database, store and move on (not sure about this one, as it might be better to throw an exception instead)
        if (! $this->labOrderInDatabase()) {
            $this->createLabOrder();
        }

        // lab test for the given patient is not in the database, which is a serious problem
        if (! $this->labTestInDatabase($lab_component)) {
            throw new Exception("This lab test has not been ordered for the patient");
        }

        // lab test id for the current component to have the results updated
        $lab_test_id = $this->getLabTestId($lab_component);
        $unit_id = $this->findUnitId($unit);

        $query = "UPDATE `PatientLabTests` SET `UnitId` = :UnitId WHERE `PatientLabTestId` = :PatientLabTestId;";
        $stmt_change_units = $this->con->prepare($query);
        $stmt_change_units->bindParam(":UnitId", $unit_id);
        $stmt_change_units->bindParam(":PatientLabTestId", $lab_test_id);
        $stmt_change_units->execute();

    }
}