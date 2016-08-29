<?php
class Physician
{
    public $first_name;
    public $middle_name;
    public $last_name;
    public $suffix;
    public $phone_number;
    public $email;
    public $specialty;

    public $con;

    private function __construct($first_name, $middle_name, $last_name, $suffix, $phone_number, $email, $specialty, $con)
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

    public function add()
    {
        $query = "INSERT INTO....";
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(":first_name", $this->first_name);
    }

    public function delete()
    {

    }

    public function lookup()
    {

    }

    // should edit everything
    public function edit()
    {

    }


}