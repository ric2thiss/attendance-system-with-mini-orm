# Cross-System Authentication Testing Guide

## Prerequisites

Before testing, ensure:
- ✅ XAMPP is running (Apache + MySQL)
- ✅ Both databases exist: `attendance-system` and `profiling-system`
- ✅ Test users exist in both databases
- ✅ PHP error logging is enabled

## Test User Setup

### Create Test Users (if not exist)

#### 1. Attendance-System Admin
```sql
-- In attendance-system database
INSERT INTO admins (username, email, password, full_name, role, is_active)
VALUES ('attendance_admin', 'attendance@test.com', '$2y$10$...', 'Attendance Admin', 'administrator', 1);
```

#### 2. Profiling-System Admin
```sql
-- In profiling-system database
INSERT INTO admin (username, password, name, email)
VALUES ('profiling_admin', '$2y$10$...', 'Profiling Admin', 'profiling@test.com');
```

#### 3. Barangay Official
```sql
-- In profiling-system database
INSERT INTO barangay_official (username, password, first_name, surname, position, email)
VALUES ('barangay_captain', '$2y$10$...', 'Juan', 'Dela Cruz', 'Barangay Captain', 'captain@test.com');
```

#### 4. Resident
```sql
-- In profiling-system database
INSERT INTO residents (username, password, first_name, surname, email)
VALUES ('resident_user', '$2y$10$...', 'Maria', 'Santos', 'resident@test.com');
```

**Note**: Replace `$2y$10$...` with actual hashed passwords using `password_hash('your_password', PASSWORD_DEFAULT)`

## Test Scenarios

### Scenario 1: Attendance-System Admin Login (via Attendance-System)

**URL**: `http://localhost/attendance-system/auth/login.php`

**Test Steps**:
1. Enter username: `attendance_admin`
2. Enter password: (your test password)
3. Click "Sign In"

**Expected Results**:
- ✅ Login successful
- ✅ Redirected to `attendance-system/admin/dashboard.php`
- ✅ Session variables set:
  ```php
  $_SESSION['admin_id'] = (attendance admin ID)
  $_SESSION['user_id'] = (attendance admin ID)
  $_SESSION['admin_username'] = 'attendance_admin'
  $_SESSION['username'] = 'attendance_admin'
  $_SESSION['admin_role'] = 'administrator'
  $_SESSION['role'] = 'administrator'
  $_SESSION['is_authenticated'] = true
  $_SESSION['auth_source'] = 'attendance_admin'
  ```

**Verify**:
- Can access attendance-system dashboard
- Can navigate to `http://localhost/dashboard.php` without re-login

---

### Scenario 2: Profiling-System Admin Login (via Attendance-System)

**URL**: `http://localhost/attendance-system/auth/login.php`

**Test Steps**:
1. Enter username: `profiling_admin`
2. Enter password: (your test password)
3. Click "Sign In"

**Expected Results**:
- ✅ Login successful (authenticated via profiling-system.admin table)
- ✅ Redirected to `attendance-system/admin/dashboard.php`
- ✅ Session variables set:
  ```php
  $_SESSION['admin_id'] = (profiling admin ID)
  $_SESSION['user_id'] = (profiling admin ID)
  $_SESSION['admin_username'] = 'profiling_admin'
  $_SESSION['username'] = 'profiling_admin'
  $_SESSION['admin_role'] = 'admin'
  $_SESSION['role'] = 'admin'
  $_SESSION['is_authenticated'] = true
  $_SESSION['auth_source'] = 'profiling_admin'
  ```

**Verify**:
- Can access attendance-system dashboard
- Can navigate to `http://localhost/dashboard.php` without re-login

---

### Scenario 3: Barangay Official Login (via Attendance-System)

**URL**: `http://localhost/attendance-system/auth/login.php`

**Test Steps**:
1. Enter username: `barangay_captain`
2. Enter password: (your test password)
3. Click "Sign In"

**Expected Results**:
- ✅ Login successful (authenticated via profiling-system.barangay_official table)
- ✅ Redirected to `attendance-system/admin/dashboard.php`
- ✅ Session variables set:
  ```php
  $_SESSION['admin_id'] = (barangay official ID)
  $_SESSION['user_id'] = (barangay official ID)
  $_SESSION['admin_username'] = 'barangay_captain'
  $_SESSION['username'] = 'barangay_captain'
  $_SESSION['admin_role'] = 'Barangay Captain'
  $_SESSION['role'] = 'Barangay Captain'
  $_SESSION['is_authenticated'] = true
  $_SESSION['auth_source'] = 'profiling_barangay_official'
  ```

**Verify**:
- Can access attendance-system dashboard
- Can navigate to `http://localhost/dashboard.php` without re-login

---

### Scenario 4: Resident Login (via Attendance-System)

**URL**: `http://localhost/attendance-system/auth/login.php`

**Test Steps**:
1. Enter username: `resident_user`
2. Enter password: (your test password)
3. Click "Sign In"

**Expected Results**:
- ✅ Login successful (authenticated via profiling-system.residents table)
- ✅ Redirected to `attendance-system/admin/dashboard.php`
- ✅ Session variables set:
  ```php
  $_SESSION['admin_id'] = (resident ID)
  $_SESSION['user_id'] = (resident ID)
  $_SESSION['admin_username'] = 'resident_user'
  $_SESSION['username'] = 'resident_user'
  $_SESSION['admin_role'] = 'resident'
  $_SESSION['role'] = 'resident'
  $_SESSION['is_authenticated'] = true
  $_SESSION['auth_source'] = 'profiling_resident'
  ```

**Verify**:
- Can access attendance-system dashboard
- Can navigate to `http://localhost/dashboard.php` without re-login

---

### Scenario 5: Profiling-System Admin Login (via Profiling-System)

**URL**: `http://localhost/login.php`

**Test Steps**:
1. Enter username: `profiling_admin`
2. Enter password: (your test password)
3. Click "Sign in"

**Expected Results**:
- ✅ Login successful
- ✅ Redirected to `dashboard.php`
- ✅ Session variables set (both profiling and attendance keys)

**Verify**:
- Can access `dashboard.php`
- Can click "Attendance Module" link
- Should access `attendance-system` without re-login

---

### Scenario 6: Barangay Official Login (via Profiling-System)

**URL**: `http://localhost/login.php`

**Test Steps**:
1. Enter username: `barangay_captain`
2. Enter password: (your test password)
3. Click "Sign in"

**Expected Results**:
- ✅ Login successful
- ✅ Redirected to `dashboard.php`
- ✅ Session variables set (both profiling and attendance keys)

**Verify**:
- Can access `dashboard.php`
- Can click "Attendance Module" link
- Should access `attendance-system` without re-login

---

### Scenario 7: Resident Login (via Profiling-System)

**URL**: `http://localhost/login.php`

**Test Steps**:
1. Enter username: `resident_user`
2. Enter password: (your test password)
3. Click "Sign in"

**Expected Results**:
- ✅ Login successful
- ✅ Redirected to `dashboard.php`
- ✅ Session variables set (both profiling and attendance keys)

**Verify**:
- Can access `dashboard.php`
- Can click "Attendance Module" link
- Should access `attendance-system` without re-login

---

### Scenario 8: Invalid Credentials

**URL**: `http://localhost/attendance-system/auth/login.php` (or `login.php`)

**Test Steps**:
1. Enter username: `nonexistent_user`
2. Enter password: `wrong_password`
3. Click "Sign In"

**Expected Results**:
- ❌ Login failed
- ❌ Error message: "Invalid username or password"
- ❌ No session variables set
- ❌ Remains on login page

**Verify**:
- Error message is generic (no info about which table was checked)
- No redirect occurs

---

### Scenario 9: Locked Attendance-System Admin

**Setup**:
```sql
UPDATE admins SET is_active = 0 WHERE username = 'attendance_admin';
```

**URL**: `http://localhost/attendance-system/auth/login.php`

**Test Steps**:
1. Enter username: `attendance_admin`
2. Enter correct password
3. Click "Sign In"

**Expected Results**:
- ❌ Login failed
- ❌ Error message: "Your account has been locked by the administrator. Please contact support for assistance."
- ❌ No session variables set

**Cleanup**:
```sql
UPDATE admins SET is_active = 1 WHERE username = 'attendance_admin';
```

---

### Scenario 10: Cross-System Navigation

**Test Steps**:
1. Log in via `login.php` as any user
2. Navigate to `dashboard.php` (should work)
3. Click "Attendance Module" link
4. Should redirect to `attendance-system/auth/login.php`
5. Should auto-redirect to `attendance-system/admin/dashboard.php` (already authenticated)

**Expected Results**:
- ✅ No re-login required
- ✅ Seamless access to attendance-system

---

## Session Verification Script

Create a test file to verify session variables:

**File**: `test_session.php` (in htdocs root)

```php
<?php
session_start();
echo "<h1>Session Variables</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
```

**Usage**:
1. Log in via any method
2. Navigate to `http://localhost/test_session.php`
3. Verify all expected session variables are set

---

## Error Log Checking

**Location**: `C:\xampp\php\logs\php_error_log`

**What to look for**:
- Database connection errors
- Authentication failures
- PDO exceptions

**Example log entries**:
```
[24-Jan-2026 21:00:00] Profiling DB connection failed: SQLSTATE[HY000] [1049] Unknown database 'profiling-system'
[24-Jan-2026 21:00:01] Profiling admin check failed: SQLSTATE[42S02]: Base table or view not found
```

---

## Debugging Tips

### Issue: Login fails for known user

**Debug Steps**:
1. Check if user exists in database:
   ```sql
   SELECT * FROM admins WHERE username = 'your_username';
   SELECT * FROM admin WHERE username = 'your_username';
   SELECT * FROM barangay_official WHERE username = 'your_username';
   SELECT * FROM residents WHERE username = 'your_username';
   ```

2. Verify password hash:
   ```php
   <?php
   $password = 'your_password';
   $hash = password_hash($password, PASSWORD_DEFAULT);
   echo "Hash: " . $hash . "\n";
   
   // Test verification
   $stored_hash = '$2y$10$...'; // from database
   echo "Verify: " . (password_verify($password, $stored_hash) ? 'YES' : 'NO');
   ?>
   ```

3. Check PHP error logs

4. Add debug output to `AuthController.php`:
   ```php
   error_log("Checking attendance-system admins table for: " . $usernameOrEmail);
   error_log("Checking profiling-system admin table for: " . $usernameOrEmail);
   ```

### Issue: Session variables not set

**Debug Steps**:
1. Add to login success block:
   ```php
   error_log("Session variables: " . print_r($_SESSION, true));
   ```

2. Check if session is started:
   ```php
   echo "Session status: " . session_status();
   // 0 = disabled, 1 = none, 2 = active
   ```

3. Verify session cookie is set in browser

### Issue: Can't access attendance-system after profiling-system login

**Debug Steps**:
1. Check if `$_SESSION['is_authenticated']` is set
2. Check if `$_SESSION['admin_id']` is set
3. Verify `AuthController::check()` returns true
4. Add debug output to `auth/login.php`:
   ```php
   if (AuthController::check()) {
       error_log("Already authenticated, redirecting to dashboard");
   }
   ```

---

## Test Checklist

Use this checklist to track your testing progress:

- [ ] Scenario 1: Attendance-System Admin Login (via Attendance-System)
- [ ] Scenario 2: Profiling-System Admin Login (via Attendance-System)
- [ ] Scenario 3: Barangay Official Login (via Attendance-System)
- [ ] Scenario 4: Resident Login (via Attendance-System)
- [ ] Scenario 5: Profiling-System Admin Login (via Profiling-System)
- [ ] Scenario 6: Barangay Official Login (via Profiling-System)
- [ ] Scenario 7: Resident Login (via Profiling-System)
- [ ] Scenario 8: Invalid Credentials
- [ ] Scenario 9: Locked Attendance-System Admin
- [ ] Scenario 10: Cross-System Navigation
- [ ] Session variables verified for all scenarios
- [ ] Error logs checked for issues
- [ ] No breaking changes to existing functionality

---

## Success Criteria

✅ All test scenarios pass  
✅ Session variables are set correctly  
✅ No errors in PHP error logs  
✅ Cross-system navigation works seamlessly  
✅ Existing functionality not broken  
✅ Security measures in place (password verification, account locking)  

---

## Rollback Plan

If issues are found:

1. **Restore AuthController.php**:
   - Revert to original version (check git history)
   - Remove profiling-system authentication methods

2. **Restore login.php**:
   - Remove attendance-system session variables
   - Keep only profiling-system session variables

3. **Clear sessions**:
   ```php
   <?php
   session_start();
   session_destroy();
   ?>
   ```

4. **Restart Apache** in XAMPP

---

**Testing Date**: _____________  
**Tested By**: _____________  
**Status**: ⏳ Pending / ✅ Passed / ❌ Failed  
**Notes**: _____________________________________________
