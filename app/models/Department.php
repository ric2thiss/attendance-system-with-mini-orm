<?php

require_once __DIR__ . '/../../app/models/Model.php';

class Department extends Model {
    protected $table = "departments";
    protected $fillable = ["department_id", "department_name"];
}