<?php
/**
 * Lookup Lab service that searches and returns relevant lab-related objects
 * 
 * Public Method List
 *  - searchLabComponents
 *  - searchLabs
 *  - searchLabOrders

 * (Finished NOT tested)
 */

class LookupLabs
{
    /**
     * @var PDO $con Database connection
     */
    private $con;

    /**
     * Initializes the LookupLab service.  Can search lab components, labs, and lab orders
     * @param PDO $con The database connection passed to the object
     * @return void
     */
    public function __construct(PDO $con)
    {
        $this->con = $con;
    }

    /**
     * Searches through the available lab components for a match
     * @param string $lab_component_name The name of the lab component to search for
     * @return LabComponent[] An array of lab component objects matching the description
     */
    public function searchLabComponents($lab_component_name)
    {
        if (! is_string($lab__component_name)) {
            throw new Exception("The lab component name provided ($lab_component_name) should be a string");
        }

        // Add wildcard for sql query
        $lab_component_name .= "%";

        $query = "SELECT `LabTestsComponents`.`LabTestComponent`, `Units`.`Unit` FROM `LabTestsComponents`
                    INNER JOIN `Units` ON `LabTestsComponent`.`LabTestComponentDefaultUnitId` = `Units`.`UnitId`
                    WHERE `LabTestsComponents`.`LabTestComponent` LIKE :LabTestComponent;";
        $stmt_lab_component = $this->con->prepare($query);
        $stmt_lab_component->bindParam(":LabTestComponent", $lab_component_name);
        $stmt_lab_component->execute();

        // Go through each lab component and create new object to put in an array
        $lab_component_array = array();
        while ($lab_component = $stmt_lab_component->fetch()) {
            $lab_component_array[] = (new LabComponent($lab_component['LabTestComponent'], $lab_component['Unit'], $this->con));;
        }

        return $lab_component_array;
    }

    /**
     * Searches through the available lab tests (aka lab panels) for a match
     * @param string $lab_name The name of the lab to search for
     * @return Lab[] An array of lab objects matching the description
     */
    public function searchLabs($lab_name)
    {
        if (! is_string($lab_name)) {
            throw new Exception("The lab name provided ($lab_name) should be a string");
        }

        // Add wildcard for sql query
        $lab_name .= "%";

        // Query to retrieve all labs with their respective components
        $query =-"SELECT `LabTests`.`LabTest`, `LabTests`.`Cost`, `LabTestsComponents`.`LabTestComponent`, `Units`.`Unit`
                    FROM `LabTests`
                    INNER JOIN `LabComponentsAssociation` ON `LabTests`.`LabTestId` = `LabComponentsAssociation`.`LabTestId`
                    INNER JOIN `LabTestsComponents` ON `LabComponentsAssociation`.`LabTestComponentId` = `LabTestsComponents`.`LabTestComponentId`
                    INNER JOIN `Units` ON `LabTestsComponents`.`LabTestComponentDefaultUnitId` = `Units`.`UnitId`
                    WHERE `LabTests`.`LabTest` LIKE :LabTest
                    ORDER BY `LabTests`.`LabTest`;";
        $stmt_labs = $this->con->prepare($query);
        $stmt_labs->bindParam(":LabTest", $lab_name);
        $stmt_labs->execute();

        // Initialize variable to be used in loop through labs with lab components
        $lab_array = array();
        $previous_lab_name = null;
        $previous_lab_cost = 0.0;
        $current_lab_name = null;
        $current_lab_cost = 0.0;
        $lab_component_array = array();

        // Loop through lab components
        while ($lab_information = $stmt_labs->fetch()) {
            $current_lab_name = $lab_information['LabTest'];
            $current_lab_cost = $lab_information['Cost'];

            // Lab information is ordered by lab name, so a new lab name means we have run out of components for the current lab
            if ($current_lab_name != $previous_lab_name && $current_lab_cost != $previous_lab_cost) {

                // Deals with edge case of first time through loop
                if ($previous_lab_name !== null) {

                    // We have hit a new lab, add all previous info to a new lab in the array to be returned
                    $lab_array[] = (new Lab($current_lab_name, $current_lab_cost, $lab_component_array, $this->con));
                }

                // Reset lab_component array for the new lab
                $lab_component_array = array();
            }

            // Add lab components for current lab into an array
            $lab_component_array[] = (new LabComponent($lab_information['LabTestComponent'], $lab_information['Unit'], $this->con));
            
            // Save current state for next loop pass through
            $previous_lab_name = $current_lab_name;
            $previous_lab_cost = $current_lab_cost;
        }

        return $lab_array;
    }

    /**
     * Search through the available lab order for match based on the info provided
     * @param string $physician_last_name The last name of the physician ordering the lab
     * @param string $patient_last_name The last name of the patient the lab was ordered for
     * @param string $date_ordered The date that the lab was ordered
     * @param string $date_results The date the lab results came in
     * @param string $lab_name The name of the lab that was ordered
     * @param string $lab_component_name The name of the lab component that was ordered
     * @return LabOrder[] An array of lab order objects
     */
    public function searchLabOrders($physician_last_name, $patient_last_name, $date_ordered, $date_results, $lab_name, $lab_component_name)
    {
        if (! is_string($physician_last_name) && ! is_string($patient_last_name) && ! is_string($lab_name) && ! is_string($lab_component_name)) {
            throw new Exception("The physician name ($physician_last_name), patient name ($patient_last_name), lab name ($lab_name), and lab component ($lab_component_name) must be strings");
        }

        if (! preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date_ordered) && ! preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date_results)) {
            throw new Exception("The order date ($date_ordered) and the results date ($date_results) must be dates in the form YYYY-MM-DD");
        }

        // Add wildcard to variables for sql query
        $physician_last_name .= "%";
        $patient_last_name .= "%";
        $date_ordered .= "%";
        $date_results .= "%";
        $lab_name .= "%";
        $lab_component_name .= "%";

        // Query to retrieve all lab orders with their information (lab, patient, physician, date ordered)
        $query = "SELECT `LabTests`.`LabTest`,
                        `LabTests`.`Cost`,
                        `LabTestsComponents`.`LabTestComponent`,
                        `Units`.`Unit`,
                        `Patient`.`fname` AS `PatientFirstName`,
                        `Patient`.`lname` AS `PatientLastName`,
                        `Patient`.`dob` AS `PatientDob`,
                        `Patient`.`address_street` AS `PatientAddress`,
                        `Patient`.`phone_number` AS `PatientPhoneNumber`,
                        `Patient`.`email_address` AS `PatientEmail`,
                        `Physician`.`FirstName` AS `PhysicianFirstName`,
                        `Physician`.`MiddleName` AS `PhysicianMiddleName`,
                        `Physician`.`LastName` AS `PhysicianLastName`,
                        `Physician`.`Suffix` AS `PhysicianSuffix`,
                        `Physician`.`PhoneNumber` AS `PhysicianPhoneNumber`,
                        `Physician`.`Email` AS `PhysicianEmail`,
                        `Specialties`.`Specialty`,
                        `PatientLabTests`.`DateOrdered`
                    FROM `PatientLabTests`
                    INNER JOIN `Patient` ON `PatientLabTests`.`PatientId` = `Patient`.`PatientId`
                    INNER JOIN `Physicians` ON `PatientLabTests`.`PhysicianId` = `Physicians`.`Physicianid`
                    INNER JOIN `Specialties` ON `Specialties`.`SpecialtyId` = `Physicians`.`SpecialtyId`
                    INNER JOIN `LabComponentsAssociation` ON `LabComponentsAssociation`.`LabComponentAssociationId` = `PatientLabTests`.`LabComponentAssociationId`
                    INNER JOIN `LabTests` ON `LabTests`.`LabTestId` = `LabComponentsAssociation`.`LabTestId`
                    INNER JOIN `LabTestsComponents` ON `LabComponentsAssociation`.`LabComponentId` = `LabTestsComponents`.`LabComponentId`
                    INNER JOIN `Units` ON `LabTestsComponents`.`LabTestComponentDefaultUnitId` = `Units`.`UnitId`
                    WHERE `Physicians`.`LastName` LIKE :PhysicianLastName
                        AND `Patient`.`lname` LIKE :PatientLastName
                        AND `PatientLabTests`.`DateOrdered` LIKE :DateOrdered
                        AND `PatientLabTests`.`DateResults` LIKE :DateResults
                        AND `LabTests`.`LabTest` LIKE :LabTest
                        AND `LabTestComponents` LIKE :LabTestComponents;";
        $stmt_lab_order = $this->con->prepare($query);
        $stmt_lab_order->bindParam(":PhysicianLastName", $physician_last_name);
        $stmt_lab_order->bindParam(":PatientLastName", $patient_last_name);
        $stmt_lab_order->bindParam(":DateOrdered", $date_ordered);
        $stmt_lab_order->bindParam(":DateResults", $date_results);
        $stmt_lab_order->bindParam(":LabTest", $lab_name);
        $stmt_lab_order->bindParam(":LabTestComponents", $lab_component_name);
        $stmt_lab_order->execute();

        // Initialize variables to be used for looping through lab orders
        $lab_order_array = array();
        $lab = null;
        $current_physician = null;
        $current_patient = null;
        $current_lab_name = null;
        $current_date_ordered = null;
        $previous_physician = null;
        $previous_patient = null;
        $previous_lab_name = null;
        $previous_date_ordered = null;
        $lab_component_array = array();

        // Go through information from database and package into objects
        while ($lab_order_information = $stmt_lab_order->fetch()) {
            $current_physician = new Physician($lab_order_information['PhysicianFirstName'], $lab_order_information['PhysicianMiddleName'], $lab_order_information['PhysicianLastName'], $lab_order_information['PhysicianSuffix'], $lab_order_information['PhysicianPhoneNumber'], $lab_order_information['PhysicianEmail'], $lab_order_information['Specialty'], $this->con);
            $current_patient = new Patient($lab_order_information['PatientFirstName'], $lab_order_information['PatientLastName'], $lab_order_information['PatientDob'], $lab_order_information['PatientAddress'], $lab_order_information['PatientPhoneNumber'], $lab_order_information['PatientEmail'], $this->con);
            $current_lab_name = $lab_information['LabTest'];
            $current_date_ordered = $lab_order_information['DateOrdered'];

            // If any of these have changed, then we are at another lab order
            if ($current_physician != $previous_physician && $current_patient != $previous_patient && $current_lab_name != $previous_lab_name && $current_date_ordered != $previous_date_ordered) {
                
                // Address edge case for first pass where nothing is filled
                if ($previous_physician != null && $previous_patient != null && $previous_lab_name != null && $previous_date_ordered != null) {
                    
                    // New lab order (which is specific to lab [set of lab components]); register new lab and store lab order object
                    $lab = new Lab($lab_information['LabTest'], $lab_order_information['Cost'], $lab_component_array, $this->con);
                    $lab_order_array[] = new LabOrder($lab, $current_patient, $current_physician, $current_date_ordered, $this->con);
                }

                // Reset lab components for next lab order
                $lab_component_array = array();
            }

            // Add lab components to lab component array for use in lab object for lab order
            $lab_component_array[] = new LabComponent($lab_order_information['LabTestComponent'], $lab_order_information['Unit'], $this->con);

            // Save previous information for next loop pass through
            $previous_physician = $current_physician;
            $previous_patient = $current_patient;
            $previous_lab_name = $current_lab_name;
            $previous_date_ordered = $current_date_ordered;
        }

        return $lab_order_array;
    }
}