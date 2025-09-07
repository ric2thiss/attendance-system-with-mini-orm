<?php

class FingerprintsController {
    protected $fingerprints;

    public function __construct()
    {
        $db = (new Database())->connect();
        $this->fingerprints = new Fingerprints($db);
    }

    public function index() 
    {
        return $this->fingerprints->all(["employee_id", "template"]);
    }
}