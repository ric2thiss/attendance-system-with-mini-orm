# Database Schema - Attendance System (v2)

**Database Name**: `attendance-system`  
**Engine**: InnoDB  
**Charset**: utf8mb4  
**Collation**: utf8mb4_general_ci  
**Server**: MariaDB 10.4.32

---

## Architecture Notes

- Residents are owned by `profiling-system` and accessed read-only.
- `attendance-system` stores only `resident_id` references.
- Cross-database foreign keys are not enforced; application code validates.

---

## Table Overview

Total Tables: **14**

1. `activity_types`
2. `admins`
3. `archive_employees`
4. `attendances`
5. `attendance_windows`
6. `departments`
7. `employees`
8. `employee_activity`
9. `employee_fingerprints`
10. `position`
11. `settings`
12. `verification_log`
13. `verification_tokens`
14. `visitor_logs`

---

## Core Tables (Key Columns)

### `employees`

- **Primary Key**: `employee_id` (VARCHAR)
- **Key Columns**:
  - `resident_id` (INT) → `profiling-system.residents.id` (logical reference)
  - `position_id` (INT) → `position.position_id`
  - `department_id` (INT, nullable) → `departments.department_id`
  - `hired_date` (DATE)

### `attendances`

- **Primary Key**: `id` (BIGINT AUTO_INCREMENT)
- **Key Columns**:
  - `employee_id` (VARCHAR) → `employees.employee_id`
  - `window` (VARCHAR)
  - `timestamp` (TIMESTAMP)

### `employee_fingerprints`

- **Primary Key**: `id` (INT AUTO_INCREMENT)
- **Key Columns**:
  - `employee_id` (VARCHAR) → `employees.employee_id`
  - `template` (LONGTEXT)

### `attendance_windows`

- **Primary Key**: `window_id` (INT AUTO_INCREMENT)
- **Key Columns**:
  - `label` (VARCHAR, UNIQUE)
  - `start_time` (TIME)
  - `end_time` (TIME)

### `visitor_logs`

- **Primary Key**: `id` (BIGINT AUTO_INCREMENT)
- **Key Columns**:
  - `resident_id` (INT, nullable) → `profiling-system.residents.id` (logical reference)
  - `is_resident` (TINYINT)
  - `purpose` (VARCHAR)

---

## Supporting Tables

- `admins` - Admin accounts and roles
- `archive_employees` - Archived employee data
- `departments` - Department catalog
- `position` - Position catalog
- `employee_activity` - Activity tracking
- `activity_types` - Activity type catalog
- `settings` - Application configuration
- `verification_log` - Verification audit log
- `verification_tokens` - Time-limited verification tokens

---

## External Dependencies (Read-Only)

- `profiling-system.residents`  
  Used for resident identity and profile data. The attendance system must never
  insert, update, or delete resident records.

---

## Source of Truth

- Use `database/attendance-system/attendance-systemv2.sql` for schema setup.
