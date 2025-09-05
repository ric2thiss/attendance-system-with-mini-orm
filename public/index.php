<?php
require_once __DIR__ . "/../bootstrap.php";

$db = (new Database())->connect();
new Attendance($db);

$updated = Attendance::query()
    ->where("employee_id", "123")
    ->update([
        "window" => "afternoon_out",
        "updated_at" => date("Y-m-d H:i:s")
    ]);

echo "Rows updated: $updated";
$attendance = Attendance::query()
    ->where("employee_id", "123")
    ->get();

// print_r($attendance);



// $attendances = Attendance::query()->where("window", "afternoon_in")->first();

// print_r($attendances);
// $newAttendanceData = [
//     "employee_id" => 123,
//     "created_at" => date("Y-m-d H:i:s"),
//     "updated_at" => date("Y-m-d H:i:s"),
//     "window" => "IN",       // or "OUT"
//     "extra_column" => "ignore this"  // ❌ will be ignored because not fillable
// ];

// try {
//     // Create a new attendance record
//     $attendanceId = Attendance::create($newAttendanceData);

//     echo "New attendance record inserted with ID: $attendanceId";
// } catch (Exception $e) {
//     echo "Error: " . $e->getMessage();
// }

// $updated = Attendance::query()
//     ->where("employee_id", "123")
//     ->update([
//         "window" => "afternoon_out",
//         "updated_at" => date("Y-m-d H:i:s")
//     ]);

// echo $updated; // 0 if nothing matched, 1+ if updated

// Attendance::updateById(5, ["window" => "OUT"]);





// $attendances = Attendance::query()->table("attendances")
//                         ->select("attendances.employee_id as AttendancesEmpId, attendances.window as window", "fingerprints.*")
//                         ->join("fingerprints", "attendances.employee_id", "=", "fingerprints.employee_id")
//                         ->where("window", "afternoon_in")->get();

// foreach ($attendances as $attendance) {
//     echo "Employee ID: " . $attendance['AttendancesEmpId'] . "<br>";
//     echo "Window: " . $attendance['window'] . "<br>";
//     echo "Fingerprint ID: " . $attendance['template'] . "<br><br>";
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

