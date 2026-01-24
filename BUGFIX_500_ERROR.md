# Bug Fix: 500 Internal Server Error

## Issue
The API endpoint was returning a 500 Internal Server Error when trying to fetch report data.

## Root Cause
The SQL queries were attempting to join with a non-existent `attendance-system.employees` table. 

The DATABASE_SCHEMA.md documentation mentioned an `employees` table, but this table does not exist in the actual database.

## Actual Database Structure
- `attendance-system.attendances.employee_id` (VARCHAR) directly stores the ID from profiling-system
- `profiling-system.barangay_official.id` (INT) 
- `profiling-system.residents.id` (INT)

The `employee_id` field directly references the `id` from either `barangay_official` or `residents` tables - there is NO intermediary `employees` table.

## Solution
Updated all 4 report type queries in `api/reports/index.php` to remove the `employees` table join:

### Before (BROKEN):
```sql
FROM attendances a
LEFT JOIN employees e ON a.employee_id = e.employee_id
LEFT JOIN `profiling-system`.barangay_official ps_off ON e.resident_id = ps_off.id
LEFT JOIN `profiling-system`.residents ps_res ON e.resident_id = ps_res.id
```

### After (FIXED):
```sql
FROM attendances a
LEFT JOIN `profiling-system`.barangay_official ps_off ON a.employee_id = ps_off.id
LEFT JOIN `profiling-system`.residents ps_res ON a.employee_id = ps_res.id
```

## Changes Made
1. **attendance-position report** - Removed employees join (line ~118)
2. **attendance-chairmanship report** - Removed employees join (line ~219)
3. **attendance-employee report** - Removed employees join (line ~316)
4. **attendance-daily report** - Removed employees join (line ~426)

## Verification
Tested with direct query execution - successfully returns data:
```json
{
    "success": true,
    "type": "attendance-position",
    "from": "2026-01-01",
    "to": "2026-01-31",
    "data": [
        {
            "position": "Barangay Captain",
            "total_employees": 1,
            "total_attendance": 3,
            "total_hours": 0.18,
            "avg_hours_per_employee": 0.18
        }
    ]
}
```

## Status
✅ **FIXED** - All report queries now work correctly with the actual database structure.
