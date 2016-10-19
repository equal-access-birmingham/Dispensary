<?php
class Prescription
{
    public $drug;
    public $physician;


    public function __construct(Drug $drug, Physician $physician) {
        $this->drug = $drug;
    }

    $this->drug->generic_name;
}

// $prescription = new Prescription;
// $prescription->sayHello();