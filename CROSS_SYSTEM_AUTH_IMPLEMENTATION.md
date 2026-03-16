# Cross-System Authentication Implementation

## Overview
This document describes the implementation of cross-system authentication between the **attendance-system** and **profiling-system**, allowing users to authenticate via either system and access both seamlessly.

## Implementation Date
2026-01-24

## Authentication Flow

### Strict Authentication Order (Attendance-System)
When a user attempts to log in through `attendance-system/auth/login.php`, the system follows this strict order:

1. **Attendance-System Database** (`attendance-system`)
   - Check `admins` table
   - If matched → Allow login

2. **Profiling-System Database** (`profiling-system`) - If step 1 fails
   - Check `admin` table
   - If matched → Allow login

3. **Profiling-System Database** - If step 2 fails
   - Check `barangay_official` table
   - If matched → Allow login

4. **Profiling-System Database** - If step 3 fails
   - Check `residents` table
   - If matched → Allow login

5. **No Match Found**
   - Deny login with "Invalid username or password"

### Profiling-System Login Flow
When a user logs in through `login.php` (profiling-system), the existing flow is maintained:

1. Check `admin` table
2. Check `barangay_official` table
3. Check `residents` table

**Enhancement**: Now also sets attendance-system compatible session variables.

## Session Variable Mapping

### Profiling-System Session Variables (login.php)
```php
$_SESSION['user_id']      // User ID
$_SESSION['username']     // Username
$_SESSION['role']         // User role (admin, position, or resident)
$_SESSION['name']         // Full name
```

### Attendance-System Session Variables
```php
$_SESSION['admin_id']         // User ID
$_SESSION['admin_username']   // Username
$_SESSION['admin_email']      // Email address
$_SESSION['admin_full_name']  // Full name
$_SESSION['admin_role']       // User role
$_SESSION['is_authenticated'] // Authentication flag (true)
$_SESSION['login_time']       // Login timestamp
```

### Unified Session Structure
Both systems now set **all** session variables to ensure compatibility:

| Purpose | Profiling-System Key | Attendance-System Key | Value Source |
|---------|---------------------|----------------------|--------------|
| User ID | `user_id` | `admin_id` | `id` from database |
| Username | `username` | `admin_username` | `username` from database |
| Email | N/A | `admin_email` | `email` from database (or empty string) |
| Full Name | `name` | `admin_full_name` | Constructed from `first_name` + `surname` or `name` field |
| Role | `role` | `admin_role` | Role/position from database |
| Auth Flag | N/A | `is_authenticated` | `true` |
| Login Time | N/A | `login_time` | `time()` |
| Auth Source | N/A | `auth_source` | Tracking field (e.g., 'profiling_admin', 'attendance_admin') |

## Modified Files

### 1. `attendance-system/app/controller/AuthController.php`
**Changes Made:**
- Added `$profilingDbConnection` property to maintain profiling-system database connection
- Added `getProfilingDbConnection()` method to establish connection to profiling-system database
- Added `authenticateProfilingSystem()` method to check profiling-system tables in order
- Added `setCompatibleSessionVariables()` method to set both system's session variables
- Modified `login()` method to implement the strict authentication flow
- Fixed PHP 8+ deprecation warning in `requireAuth()` method

**Key Features:**
- Separate database connections (no schema changes)
- Secure password verification for all tables
- Error logging for debugging
- Backward compatibility maintained
- Account locking support for attendance-system admins

### 2. `login.php` (Profiling-System)
**Changes Made:**
- Extended admin authentication to set attendance-system session variables
- Extended barangay_official authentication to set attendance-system session variables
- Extended residents authentication to set attendance-system session variables

**Key Features:**
- No breaking changes to existing logic
- All existing session variables preserved
- Additional session variables added for attendance-system compatibility

## Database Connections

### Attendance-System Database
- **Database Name**: `attendance-system`
- **Connection**: Managed by `Database` class in `app/database/Database.php`
- **Tables Used**: `admins`

### Profiling-System Database
- **Database Name**: `profiling-system`
- **Connection**: Created in `AuthController::getProfilingDbConnection()`
- **Tables Used**: `admin`, `barangay_official`, `residents`

**Important**: Both databases use separate connections. No schema merging or alteration.

## Security Features

### Password Verification
- **Attendance-System Admins**: Uses `AdminRepository::verifyPassword()` with `password_verify()`
- **Profiling-System Users**: Supports both hashed and plain passwords for legacy compatibility
  - Admin table: `password_verify()` or plain text comparison
  - Barangay Official: `password_verify()` only
  - Residents: `password_verify()` or plain text comparison

### Account Locking
- Attendance-system admins can be locked (`is_active = 0`)
- Locked accounts are detected and login is denied with appropriate message

### Error Handling
- Database connection failures are logged via `error_log()`
- Authentication failures return generic "Invalid username or password" message
- No information leakage about which table was checked

## Usage Examples

### Example 1: Attendance-System Admin Login
```
User: admin@attendance
Password: ********

Flow:
1. Check attendance-system.admins → MATCH
2. Set all session variables
3. Redirect to attendance-system dashboard
4. User can also access profiling-system without re-login
```

### Example 2: Profiling-System Admin Login via Attendance-System
```
User: admin@profiling
Password: ********

Flow:
1. Check attendance-system.admins → NO MATCH
2. Check profiling-system.admin → MATCH
3. Set all session variables
4. Redirect to attendance-system dashboard
5. User can access both systems
```

### Example 3: Barangay Official Login via Profiling-System
```
User: barangay_captain
Password: ********

Flow (in login.php):
1. Check profiling-system.admin → NO MATCH
2. Check profiling-system.barangay_official → MATCH
3. Set profiling-system AND attendance-system session variables
4. Redirect to dashboard.php
5. User can click "Attendance Module" and access without re-login
```

### Example 4: Resident Login
```
User: resident123
Password: ********

Flow (via either system):
- Profiling-system login.php: Check residents table → Set all session variables
- Attendance-system login: Check profiling-system.residents → Set all session variables
- Result: Access to both systems
```

## Backward Compatibility

### Existing Functionality Preserved
✅ All existing authentication logic in attendance-system remains intact  
✅ All existing authentication logic in profiling-system remains intact  
✅ Existing session variables are still set  
✅ No changes to UI, routing, or layouts  
✅ No database schema changes  

### New Functionality Added
✅ Attendance-system can authenticate profiling-system users  
✅ Both systems set compatible session variables  
✅ Users can access both systems with single login  
✅ Authentication source is tracked for debugging  

## Testing Checklist

- [ ] Test attendance-system admin login via attendance-system
- [ ] Test profiling-system admin login via attendance-system
- [ ] Test barangay official login via attendance-system
- [ ] Test resident login via attendance-system
- [ ] Test admin login via profiling-system login.php
- [ ] Test barangay official login via profiling-system login.php
- [ ] Test resident login via profiling-system login.php
- [ ] Verify session variables are set correctly for all user types
- [ ] Verify locked attendance-system admin accounts are blocked
- [ ] Verify cross-system access (login in one, access other)
- [ ] Test invalid credentials return proper error message
- [ ] Verify no information leakage in error messages

## Troubleshooting

### Issue: "Invalid username or password" for known user
**Possible Causes:**
1. Password mismatch (check if password is hashed correctly)
2. Database connection failure (check error logs)
3. User exists in different table than expected

**Debug Steps:**
1. Check `$_SESSION['auth_source']` to see which table authenticated (if login succeeded)
2. Check PHP error logs for database connection errors
3. Verify user exists in expected table with correct username

### Issue: User can't access attendance-system after profiling-system login
**Possible Causes:**
1. Session variables not set correctly in login.php
2. Session not started properly

**Debug Steps:**
1. Add `var_dump($_SESSION);` after login to verify all session variables are set
2. Verify `$_SESSION['is_authenticated']` is `true`
3. Check that `$_SESSION['admin_id']` matches `$_SESSION['user_id']`

### Issue: Database connection error to profiling-system
**Possible Causes:**
1. Database name incorrect
2. Database doesn't exist
3. MySQL server not running

**Debug Steps:**
1. Check error logs for PDO exception messages
2. Verify `profiling-system` database exists in phpMyAdmin
3. Verify database credentials in `AuthController::getProfilingDbConnection()`

## Future Enhancements

### Potential Improvements
- Add role-based access control (RBAC) for attendance-system features
- Implement single sign-out (logout from one system logs out from both)
- Add session timeout synchronization
- Create unified user management interface
- Add audit logging for cross-system authentication events

### Considerations
- Consider implementing OAuth2 or JWT for more robust cross-system auth
- Consider creating a shared authentication service
- Consider implementing two-factor authentication (2FA)

## Notes

- **No Duplicate Logic**: Authentication logic is centralized in `AuthController`
- **Secure by Default**: All password verifications use `password_verify()` where possible
- **Maintainable**: Clear separation of concerns between systems
- **Extensible**: Easy to add more authentication sources in the future
- **Debuggable**: `auth_source` session variable tracks authentication origin

## Contact & Support

For issues or questions regarding this implementation, refer to:
- This documentation file
- `AuthController.php` inline comments
- PHP error logs in `xampp/php/logs/`
- MySQL error logs

---

**Implementation Status**: ✅ COMPLETE  
**Tested**: ⏳ PENDING USER TESTING  
**Production Ready**: ⚠️ REQUIRES TESTING
