<?php
$employeeId = $_GET['employee_id'] ?? null;
$residentId = $_GET['resident_id'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
  <title>Biometric Success</title>
</head>
<body>
  <h3>✅ Biometrics registered successfully!</h3>
  <?php if ($employeeId): ?>
    <p>Employee ID: <?= htmlspecialchars($employeeId) ?></p>
  <?php elseif ($residentId): ?>
    <p>Resident ID: <?= htmlspecialchars($residentId) ?></p>
  <?php endif; ?>
</body>
</html>