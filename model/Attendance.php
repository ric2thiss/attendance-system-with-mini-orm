<?php
require_once __DIR__ . '/../models/Model.php';

class Attendance extends Model {
    protected $table = "attendances";
    protected $fillable = ["employee_id", "created_at", "updated_at", "window"];
}
