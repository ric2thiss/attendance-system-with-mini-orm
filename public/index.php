<?php
require_once __DIR__ . "/../bootstrap.php";

$db = (new Database())->connect();
new Attendance($db);

$attendances = Attendance::query()->where("window", "afternoon_in")->get();

print_r($attendances);
// $dbconn = new Database();

// $model = new Model($dbconn->connect());


// $attendances = $model->table("attendances")
//                         ->select("attendances.employee_id as AttendancesEmpId, attendances.window as window", "fingerprints.*")
//                         ->join("fingerprints", "attendances.employee_id", "=", "fingerprints.employee_id")
//                         ->where("window", "afternoon_in")->get();

// foreach ($attendances as $attendance) {
//     echo "Employee ID: " . $attendance['AttendancesEmpId'] . "<br>";
//     echo "Window: " . $attendance['window'] . "<br>";
//     echo "Fingerprint ID: " . $attendance['id'] . "<br><br>";
// }

// $db = (new Database())->connect();
// new Attendance($db); // bootstrap model with db

// // get all
// $all = Attendance::all();

// // find by id
// $one = Attendance::find(1);

// // where
// $list = Attendance::query()->where("status", "present")->get();

// print_r($all);
// print_r($one);
// print_r($list);

