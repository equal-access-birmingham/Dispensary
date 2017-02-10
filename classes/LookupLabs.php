<?php
class LookupLabs
{
    private $con;

    public function __construct(PDO $con)
    {
        $this->con = $con;
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
    }
}