# Database Summary - Attendance System (v2)

**Database**: `attendance-system`  
**SQL Dump**: `database/attendance-system/attendance-systemv2.sql`  
**Total Tables**: **14**  
**External Dependency**: `profiling-system.residents` (read-only)

---

## Table List

1. ✅ `activity_types` - Activity type definitions
2. ✅ `admins` - Admin accounts
3. ✅ `archive_employees` - Archived employee records
4. ✅ `attendances` - Attendance logs
5. ✅ `attendance_windows` - Attendance time windows
6. ✅ `departments` - Department data
7. ✅ `employees` - Employee records (stores `resident_id` only)
8. ✅ `employee_activity` - Employee activity tracking
9. ✅ `employee_fingerprints` - Fingerprint templates
10. ✅ `position` - Job positions
11. ✅ `settings` - Application settings
12. ✅ `verification_log` - Verification tracking
13. ✅ `verification_tokens` - Token-based verification
14. ✅ `visitor_logs` - Visitor logs (stores `resident_id` when applicable)

---

## External Residents (Read-Only)

- **Source**: `profiling-system.residents`
- **Usage**:
  - `employees.resident_id` references `profiling-system.residents.id`
  - `visitor_logs.resident_id` references `profiling-system.residents.id` when `is_resident = 1`
- **Notes**:
  - No resident tables exist in `attendance-system`
  - Cross-database foreign keys are not enforced; application code validates

---

## Key Tables for C# Application

1. **`employee_fingerprints`** - Stores fingerprint templates  
   - Endpoint: `GET api/templates/index.php`
2. **`attendances`** - Stores attendance logs  
   - Endpoint: `POST api/attendance/index.php`
3. **`employees`** - Employee records  
   - Used for employee ID validation and attendance logging
4. **`verification_tokens`** - Token storage  
   - Endpoint: `POST verify.php`, `GET verify.php?confirm={token}`

---

## Relationships (Logical)

```
profiling-system.residents (1) ──< (many) employees
profiling-system.residents (1) ──< (many) visitor_logs

employees (1) ──< (many) attendances
employees (1) ──< (many) employee_fingerprints
employees (1) ──< (many) employee_activity
employees (1) ──< (many) verification_log
employees (1) ──< (many) verification_tokens

position (1) ──< (many) employees
departments (1) ──< (many) employees
activity_types (1) ──< (many) employee_activity
```

---

## Notes

1. ✅ Attendance uses the **v2 schema only**
2. ✅ No resident tables are present in `attendance-system`
3. ✅ Residents are owned by `profiling-system` and accessed read-only
