<?php
require_once __DIR__ . '/../../app/models/Model.php';

class Attendance extends Model {
    protected $table = "attendances";
    protected $fillable = ["employee_id", "created_at", "updated_at", "window"];

    public function employee()
    {
        // Each attendance belongs to one employee
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
