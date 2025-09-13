<?php
$employeeId = $_GET['employee_id'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
  <title>Biometric Success</title>
</head>
<body>
  <h3>✅ Biometrics registered successfully!</h3>
  <p>Employee ID: <?= htmlspecialchars($employeeId) ?></p>
</body>
</html>