<?php
// Assume you already fetched employee data from your QueryBuilder model
$employeeId = 2021;
?>

ID: <?php echo $employeeId; ?>
<button onclick="window.location.href='biometrics://enroll?employee_id=<?php echo $employeeId; ?>'">
    Register Biometrics
</button>
