# Window Label Consistency Fix - Summary

## Problem
Attendance window labels were inconsistent in the database, causing issues with:
- `Morning_In` (capital M, capital I)
- `morning_out` (all lowercase)
- `morning_in` (all lowercase)

This inconsistency caused problems with attendance matching, validation, and display.

## Solution
Implemented comprehensive normalization to ensure all window labels are stored and compared in lowercase format.

## Files Modified

### 1. `app/controller/AttendanceController.php`
- **Line 130**: Normalizes incoming window values to lowercase before validation and saving
- **Line 134**: Uses case-insensitive comparison for window validation
- **Result**: All new attendance records will have lowercase window values

### 2. `app/repositories/AttendanceWindowRepository.php`
- **Line 25-30**: `findByLabel()` now uses case-insensitive comparison
- **Line 35-48**: `getWindowsArray()` normalizes window labels to lowercase when retrieving from database
- **Result**: Window lookups are case-insensitive, and API responses return normalized labels

### 3. `app/repositories/AttendanceRepository.php`
- **Line 46-56**: `existsTodayForWindow()` uses case-insensitive comparison
- **Line 142-174**: `getCorrespondingAttendance()` normalizes window values before comparison
- **Line 15-22**: `getLast()` uses case-insensitive join for window_label lookup
- **Line 71-75**: `getPaginated()` uses case-insensitive join for window_label lookup
- **Result**: All attendance queries handle window labels consistently regardless of case

### 4. `app/controller/AttendanceWindowController.php`
- **Line 76-90**: `store()` method normalizes window labels to lowercase when creating new windows
- **Line 161-178**: `update()` method normalizes window labels to lowercase when updating windows
- **Result**: Master window list entries are stored in lowercase format

## Database Migration Script

### `database/fix_window_labels.sql`
This script normalizes existing inconsistent data:

```sql
-- Step 1: Normalize attendance_windows table labels first (master data)
UPDATE `attendance_windows` 
SET `label` = LOWER(TRIM(`label`))
WHERE `label` != LOWER(TRIM(`label`));

-- Step 2: Normalize attendances table window values
UPDATE `attendances` 
SET `window` = LOWER(TRIM(`window`))
WHERE `window` != LOWER(TRIM(`window`));
```

## How It Works

### For New Records
1. When attendance is logged (via C# client or API), the window value is normalized to lowercase before saving
2. When creating/updating window definitions in the master list, labels are normalized to lowercase
3. All validations use case-insensitive comparisons

### For Existing Records
1. The SQL script fixes all existing inconsistent data
2. Queries use case-insensitive comparisons, so they work with both old and new data until the script is run
3. After running the script, all data will be consistent

### API Responses
- Window labels returned from `/api/attendance/windows.php` are normalized to lowercase
- The C# client receives lowercase labels and sends them back, maintaining consistency

## Testing Checklist

- [ ] Run the SQL migration script: `database/fix_window_labels.sql`
- [ ] Verify existing attendance records have lowercase window values
- [ ] Test creating new attendance via C# client
- [ ] Test creating new attendance via API
- [ ] Test creating/updating window definitions in master list
- [ ] Verify attendance matching (in/out pairs) works correctly
- [ ] Check that duplicate window detection works (case-insensitive)

## Benefits

1. **Consistency**: All window labels are now in lowercase format
2. **Reliability**: Case-insensitive comparisons prevent issues with mixed-case data
3. **Backward Compatibility**: Existing queries work with both old and new data formats
4. **Future-Proof**: New records automatically use normalized format

## Notes

- The C# client (`Identification.cs`) doesn't need changes - it receives normalized labels from the API
- Frontend JavaScript code doesn't need changes - it displays whatever label is provided
- All database queries now handle case-insensitive comparisons for backward compatibility