<?php

require_once __DIR__ . '/../../app/models/Model.php';

class Resident extends Model {
    protected $table = "residents";
    protected $fillable = ["name", "age"];
}
