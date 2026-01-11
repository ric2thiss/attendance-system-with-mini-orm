# Database Summary - Quick Reference

**Database**: `attendance-system`  
**Total Tables**: **18** ✅  
**SQL Dump**: `database/attendance-system (1).sql`  
**Generated**: Dec 13, 2025 at 11:49 PM

---

## Complete Table List

1. ✅ `activity_types` - Activity type definitions
2. ✅ `addresses` - Resident addresses
3. ✅ `attendances` - Attendance logs
4. ✅ `civil_status` - Civil status lookup
5. ✅ `departments` - Department data
6. ✅ `employees` - Employee records
7. ✅ `employee_activity` - Employee activity tracking
8. ✅ `family_relationships` - Family connections
9. ✅ `fingerprints` - Biometric fingerprint templates
10. ✅ `occupations` - Job information
11. ✅ `position` - Job positions
12. ✅ `residents` - Core resident information
13. ✅ `resident_biometrics` - Resident biometric data
14. ✅ `resident_contacts` - Contact information
15. ✅ `resident_ids` - ID documents
16. ✅ `resident_status` - Status information
17. ✅ `verification_log` - Verification tracking
18. ✅ `verification_tokens` - Token-based verification

---

## Key Tables for C# Application

### Used by C# Application (DO NOT MODIFY)

1. **`fingerprints`** - Stores fingerprint templates
   - Used by: Enrollment.cs, Identification.cs, Verification.cs
   - Endpoint: `GET api/services.php?resource=templates`

2. **`attendances`** - Stores attendance logs
   - Used by: Identification.cs
   - Endpoint: `POST api/services.php?resource=attendances`

3. **`employees`** - Employee records
   - Used by: All C# forms (for employee_id validation)

4. **`verification_tokens`** - Token storage
   - Used by: Verification.cs
   - Endpoint: `POST verify.php`, `GET verify.php?confirm={token}`

---

## Table Relationships

```
residents (1) ──< (many) employees
residents (1) ──< (many) addresses
residents (1) ──< (many) occupations
residents (1) ──< (many) family_relationships
residents (1) ──< (many) resident_biometrics
residents (1) ──< (many) resident_contacts
residents (1) ──< (many) resident_ids
residents (1) ──< (many) resident_status
residents (1) ──> (1) civil_status

employees (1) ──< (many) attendances
employees (1) ──< (many) fingerprints
employees (1) ──< (many) employee_activity
employees (1) ──< (many) verification_log
employees (1) ──< (many) verification_tokens
employees (1) ──> (1) position
employees (1) ──> (1) residents

position (1) ──< (many) employees
activity_types (1) ──< (many) employee_activity
```

---

## Data Counts (from SQL Dump)

- **Residents**: 3
- **Employees**: 3
- **Attendances**: 7
- **Fingerprints**: 2
- **Positions**: 2
- **Departments**: 2
- **Addresses**: 1
- **Occupations**: 1
- **Family Relationships**: 1
- **Resident Biometrics**: 1
- **Resident IDs**: 1
- **Resident Status**: 1
- **Employee Activity**: 1
- **Activity Types**: 1
- **Verification Log**: 0
- **Verification Tokens**: 0

---

## Important Notes

1. ✅ **All 18 tables are present** in the updated SQL dump
2. ✅ **`verification_tokens` table exists** - No longer missing
3. ⚠️ **Missing Foreign Keys**: Some tables don't have FK constraints but should:
   - `attendances.employee_id` → `employees.employee_id`
   - `fingerprints.employee_id` → `employees.employee_id`
   - `verification_log.employee_id` → `employees.employee_id`
   - `verification_tokens.employee_id` → `employees.employee_id`

4. **Custom Employee IDs**: `employee_id` is VARCHAR (not auto-increment)
   - Examples: "2021", "20201188", "20201197"

5. **Fingerprint Storage**: Templates stored as base64-encoded XML FMD (longtext)

6. **Token Expiry**: `verification_tokens` have 60-second expiry (enforced in PHP code)

---

## Database Files

- `database/attendance-system (1).sql` - Complete database dump (18 tables)
- `DATABASE_SCHEMA.md` - Detailed schema documentation
- `DATABASE_SUMMARY.md` - This quick reference file

---

**Status**: ✅ Complete - All tables verified and documented

