<?php
require_once __DIR__ . '/../../app/models/Model.php';

class Employee extends Model {
    protected $table = "employees";
    protected $fillable = ["employee_id", "resident_id", "position"];

    // public function resident() {
    //     return $this->belongsTo(Resident::class, 'resident_id');
    // }

    public function attendances() {
        return $this->hasMany(Attendance::class, 'employee_id');
    }
}
