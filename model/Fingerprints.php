<?php

require_once __DIR__ . '/../models/Model.php';

class Fingerprints extends Model {
    protected $table = "fingerprints";
    protected $fillable = ["employee_id", "template"];
}