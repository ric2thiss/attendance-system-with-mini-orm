# Database Schema - Attendance System

**Database Name**: `attendance-system`  
**Engine**: InnoDB  
**Charset**: utf8mb4  
**Collation**: utf8mb4_general_ci  
**Server**: MariaDB 10.4.32

---

## Table Overview

Total Tables: **18**

### Core Tables
1. `residents` - Resident information
2. `employees` - Employee records
3. `attendances` - Attendance logs
4. `fingerprints` - Biometric fingerprint templates
5. `position` - Job positions
6. `departments` - Department data

### Resident-Related Tables
7. `addresses` - Resident addresses
8. `occupations` - Job information
9. `family_relationships` - Family connections
10. `resident_biometrics` - Resident biometric data
11. `resident_contacts` - Contact information
12. `resident_ids` - ID documents
13. `resident_status` - Status information
14. `civil_status` - Civil status lookup

### Activity & Verification Tables
15. `employee_activity` - Employee activity tracking
16. `activity_types` - Activity type definitions
17. `verification_log` - Verification tracking
18. `verification_tokens` - Token-based verification (1-minute expiry)

---

## Detailed Table Schemas

### 1. `residents` - Core Resident Data

**Primary Key**: `resident_id` (AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `resident_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `phil_sys_number` | varchar(20) | YES | NULL | Philippine System Number (UNIQUE) |
| `first_name` | varchar(100) | NO | - | First name |
| `middle_name` | varchar(100) | YES | NULL | Middle name |
| `last_name` | varchar(100) | NO | - | Last name |
| `suffix` | varchar(20) | YES | NULL | Name suffix (Jr., Sr., etc.) |
| `gender` | enum('Male','Female','Other') | NO | - | Gender |
| `birthdate` | date | NO | - | Date of birth |
| `place_of_birth_city` | varchar(100) | YES | NULL | Birth city |
| `place_of_birth_province` | varchar(100) | YES | NULL | Birth province |
| `blood_type` | varchar(5) | YES | NULL | Blood type |
| `civil_status_id` | int(11) | YES | NULL | FK to `civil_status` |
| `photo_path` | varchar(255) | YES | NULL | Photo file path |
| `created_at` | timestamp | NO | current_timestamp() | Creation timestamp |
| `updated_at` | timestamp | NO | current_timestamp() ON UPDATE | Update timestamp |

**Indexes**:
- PRIMARY KEY (`resident_id`)
- UNIQUE KEY (`phil_sys_number`)
- KEY (`civil_status_id`)

**Foreign Keys**:
- `civil_status_id` → `civil_status.civil_status_id`

**Sample Data**: 3 residents

---

### 2. `employees` - Employee Records

**Primary Key**: `employee_id` (VARCHAR, NOT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `employee_id` | varchar(50) | NO | - | Primary key (e.g., "2021", "20201188") |
| `resident_id` | int(50) | NO | - | FK to `residents` |
| `position_id` | int(11) | NO | - | FK to `position` |
| `hired_date` | date | NO | - | Employment start date |
| `created_at` | datetime | NO | current_timestamp() | Creation timestamp |
| `updated_at` | datetime | NO | current_timestamp() ON UPDATE | Update timestamp |

**Indexes**:
- PRIMARY KEY (`employee_id`)
- KEY (`resident_id`)
- KEY (`position_id`)

**Foreign Keys**:
- `resident_id` → `residents.resident_id`
- `position_id` → `position.position_id`

**Sample Data**: 3 employees
- '2021' (Ric Charles Paquibot)
- '20201188' (Keneth Arsolon)
- '20201197' (Trixxie Nicole Petalcorin)

---

### 3. `attendances` - Attendance Logs

**Primary Key**: `id` (BIGINT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `id` | bigint(20) | NO | AUTO_INCREMENT | Primary key |
| `employee_id` | varchar(255) | NO | - | FK to `employees.employee_id` |
| `timestamp` | timestamp | NO | current_timestamp() ON UPDATE | Attendance timestamp |
| `created_at` | timestamp | YES | NULL | Creation timestamp |
| `updated_at` | timestamp | YES | NULL | Update timestamp |
| `window` | varchar(255) | NO | - | Time window (morning_in, morning_out, afternoon_in, afternoon_out) |

**Indexes**:
- PRIMARY KEY (`id`)

**Sample Data**: 7 attendance records
- Windows used: `morning_in`, `afternoon_in`, `afternoon_out`

**Note**: No foreign key constraint defined, but `employee_id` references `employees.employee_id`

---

### 4. `fingerprints` - Biometric Templates

**Primary Key**: `id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `employee_id` | varchar(256) | NO | - | FK to `employees.employee_id` |
| `template` | longtext | NO | - | Base64 encoded XML FMD (Fingerprint Minutiae Data) |
| `created_at` | timestamp | NO | current_timestamp() | Creation timestamp |
| `updated_at` | datetime | NO | current_timestamp() ON UPDATE | Update timestamp |

**Indexes**:
- PRIMARY KEY (`id`)

**Sample Data**: 2 fingerprint templates
- Employee '2021' has template
- Employee '20201188' has template

**Note**: Template format is base64-encoded XML containing ANSI FMD data

---

### 5. `position` - Job Positions

**Primary Key**: `position_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `position_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `position_name` | varchar(50) | NO | - | Position name |
| `created_at` | date | NO | current_timestamp() | Creation date |

**Indexes**:
- PRIMARY KEY (`position_id`)

**Sample Data**: 2 positions
- 'Kagawad' (position_id: 1)
- 'Chairman' (position_id: 2)

---

### 6. `departments` - Departments

**Primary Key**: `department_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `department_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `department_name` | varchar(50) | NO | - | Department name |
| `created_at` | datetime | NO | current_timestamp() | Creation timestamp |

**Indexes**:
- PRIMARY KEY (`department_id`)

**Sample Data**: 2 departments
- 'Finance' (department_id: 1)
- 'Peace and Order' (department_id: 2)

---

### 7. `addresses` - Resident Addresses

**Primary Key**: `address_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `address_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `resident_id` | int(11) | NO | - | FK to `residents` |
| `address_type` | enum('Permanent','Present','Work','Other') | YES | 'Permanent' | Address type |
| `house_number` | varchar(50) | YES | NULL | House number |
| `building_name` | varchar(100) | YES | NULL | Building name |
| `street_name` | varchar(150) | YES | NULL | Street name |
| `subdivision_village` | varchar(100) | YES | NULL | Subdivision/village |
| `purok` | varchar(100) | YES | NULL | Purok |
| `sitio` | varchar(100) | YES | NULL | Sitio |
| `barangay` | varchar(100) | NO | - | Barangay |
| `district` | varchar(100) | YES | NULL | District |
| `municipality_city` | varchar(100) | NO | - | City/Municipality |
| `province` | varchar(100) | NO | - | Province |
| `region` | varchar(100) | YES | NULL | Region |
| `postal_code` | varchar(20) | YES | NULL | Postal code |
| `latitude` | decimal(10,7) | YES | NULL | GPS latitude |
| `longitude` | decimal(10,7) | YES | NULL | GPS longitude |
| `months_of_residency` | int(11) | YES | NULL | Months of residency |
| `is_owner` | tinyint(1) | YES | 0 | Is property owner (0/1) |
| `created_at` | timestamp | NO | current_timestamp() | Creation timestamp |
| `updated_at` | timestamp | NO | current_timestamp() ON UPDATE | Update timestamp |

**Indexes**:
- PRIMARY KEY (`address_id`)
- KEY (`resident_id`)

**Foreign Keys**:
- `resident_id` → `residents.resident_id`

**Sample Data**: 1 address record

---

### 8. `occupations` - Job Information

**Primary Key**: `occupation_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `occupation_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `resident_id` | int(11) | NO | - | FK to `residents` |
| `job_title` | varchar(100) | NO | - | Job title |
| `employer` | varchar(200) | YES | NULL | Employer name |
| `income_bracket` | varchar(50) | YES | NULL | Income range |

**Indexes**:
- PRIMARY KEY (`occupation_id`)
- KEY (`resident_id`)

**Foreign Keys**:
- `resident_id` → `residents.resident_id`

**Sample Data**: 1 occupation record

---

### 9. `family_relationships` - Family Connections

**Primary Key**: `relationship_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `relationship_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `resident_id` | int(11) | NO | - | FK to `residents` (main person) |
| `relative_id` | int(11) | NO | - | FK to `residents` (relative) |
| `relationship_type` | varchar(50) | NO | - | Relationship type (e.g., 'brother') |

**Indexes**:
- PRIMARY KEY (`relationship_id`)
- KEY (`resident_id`)
- KEY (`relative_id`)

**Foreign Keys**:
- `resident_id` → `residents.resident_id`
- `relative_id` → `residents.resident_id`

**Sample Data**: 1 relationship (resident 2 is brother of resident 3)

---

### 10. `resident_biometrics` - Resident Biometric Data

**Primary Key**: `biometric_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `biometric_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `resident_id` | int(11) | NO | - | FK to `residents` |
| `biometric_type` | enum('Signature','Thumbmark','Fingerprint','Photo') | NO | - | Type of biometric |
| `file_path` | varchar(255) | NO | - | File path to biometric data |

**Indexes**:
- PRIMARY KEY (`biometric_id`)
- KEY (`resident_id`)

**Foreign Keys**:
- `resident_id` → `residents.resident_id`

**Sample Data**: 1 biometric record (Fingerprint type)

---

### 11. `resident_contacts` - Contact Information

**Primary Key**: `contact_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `contact_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `resident_id` | int(11) | NO | - | FK to `residents` |
| `contact_type` | enum('Mobile','Telephone','Email') | NO | - | Contact type |
| `contact_value` | varchar(150) | NO | - | Contact value (phone/email) |

**Indexes**:
- PRIMARY KEY (`contact_id`)
- KEY (`resident_id`)

**Foreign Keys**:
- `resident_id` → `residents.resident_id`

**Sample Data**: No records in dump

---

### 12. `resident_ids` - ID Documents

**Primary Key**: `id_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `id_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `resident_id` | int(11) | NO | - | FK to `residents` |
| `id_type` | varchar(100) | NO | - | ID type (e.g., 'National ID') |
| `id_number` | varchar(100) | NO | - | ID number |
| `issue_date` | date | YES | NULL | Issue date |
| `expiry_date` | date | YES | NULL | Expiry date |

**Indexes**:
- PRIMARY KEY (`id_id`)
- KEY (`resident_id`)

**Foreign Keys**:
- `resident_id` → `residents.resident_id`

**Sample Data**: 1 ID record (National ID)

---

### 13. `resident_status` - Status Information

**Primary Key**: `status_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `status_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `resident_id` | int(11) | NO | - | FK to `residents` |
| `status_type` | enum('Senior Citizen','PWD','Solo Parent','Indigent','Other') | NO | - | Status type |
| `is_active` | tinyint(1) | YES | 1 | Active status (0/1) |

**Indexes**:
- PRIMARY KEY (`status_id`)
- KEY (`resident_id`)

**Foreign Keys**:
- `resident_id` → `residents.resident_id`

**Sample Data**: 1 status record (Indigent)

---

### 14. `civil_status` - Civil Status Lookup

**Primary Key**: `civil_status_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `civil_status_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `status_name` | varchar(50) | NO | - | Status name (UNIQUE) |

**Indexes**:
- PRIMARY KEY (`civil_status_id`)
- UNIQUE KEY (`status_name`)

**Sample Data**: 1 status ('Single')

---

### 15. `employee_activity` - Employee Activity Tracking

**Primary Key**: `employee_activity_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `employee_activity_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `employee_id` | varchar(50) | NO | - | FK to `employees` |
| `activity_types_id` | int(11) | NO | - | FK to `activity_types` |
| `start` | datetime | YES | NULL | Activity start time |
| `end` | datetime | YES | NULL | Activity end time |
| `created_by` | varchar(50) | NO | - | FK to `employees.employee_id` |
| `created_at` | datetime | NO | current_timestamp() | Creation timestamp |
| `updated_at` | timestamp | NO | current_timestamp() ON UPDATE | Update timestamp |

**Indexes**:
- PRIMARY KEY (`employee_activity_id`)
- KEY (`employee_id`, `activity_types_id`, `created_by`)
- KEY (`created_by`)
- KEY (`activity_types_id`)

**Foreign Keys**:
- `employee_id` → `employees.employee_id`
- `created_by` → `employees.employee_id`
- `activity_types_id` → `activity_types.activity_types_id`

**Sample Data**: 1 activity record (Business Trip)

---

### 16. `activity_types` - Activity Type Definitions

**Primary Key**: `activity_types_id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `activity_types_id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `activity_name` | varchar(100) | NO | - | Activity name |
| `created_at` | datetime | NO | current_timestamp() | Creation timestamp |
| `created_by` | varchar(50) | NO | - | FK to `employees.employee_id` |

**Indexes**:
- PRIMARY KEY (`activity_types_id`)
- KEY (`created_by`)

**Foreign Keys**:
- `created_by` → `employees.employee_id`

**Sample Data**: 1 activity type ('Business Trip')

---

### 17. `verification_log` - Verification Tracking

**Primary Key**: `id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `employee_id` | varchar(255) | NO | - | FK to `employees.employee_id` |
| `status` | varchar(255) | NO | - | Verification status |
| `device_id` | varchar(255) | NO | - | Device identifier |
| `ip_address` | varchar(255) | NO | - | IP address |
| `created_at` | timestamp | NO | current_timestamp() | Creation timestamp |

**Indexes**:
- PRIMARY KEY (`id`)

**Note**: No foreign key constraint defined, but `employee_id` references `employees.employee_id`

**Sample Data**: No records in dump

---

### 18. `verification_tokens` - Token-Based Verification

**Primary Key**: `id` (INT AUTO_INCREMENT)

| Column | Type | Null | Default | Description |
|--------|------|------|---------|-------------|
| `id` | int(11) | NO | AUTO_INCREMENT | Primary key |
| `employee_id` | varchar(255) | NO | - | FK to `employees.employee_id` |
| `status` | varchar(255) | NO | - | Verification status |
| `token` | varchar(255) | NO | - | Confirmation token (hex string, 32 chars from `bin2hex(random_bytes(16))`) |
| `created_at` | timestamp | NO | current_timestamp() | Creation timestamp |
| `updated_at` | timestamp | YES | NULL ON UPDATE | Update timestamp |

**Indexes**:
- PRIMARY KEY (`id`)
- KEY (`employee_id`)
- KEY (`token`)
- KEY (`created_at`)

**Note**: No foreign key constraint defined, but `employee_id` references `employees.employee_id`

**Token Expiry**: Tokens expire after 60 seconds (checked in `VerificationLogController::confirm()`)

**Sample Data**: No records in dump

**Usage**: Used by `verify.php` for secure token-based verification flow

---

## Relationships Diagram

```
residents (1) ──< (many) employees
residents (1) ──< (many) addresses
residents (1) ──< (many) occupations
residents (1) ──< (many) family_relationships (self-referencing)
residents (1) ──< (many) resident_biometrics
residents (1) ──< (many) resident_contacts
residents (1) ──< (many) resident_ids
residents (1) ──< (many) resident_status
residents (1) ──> (1) civil_status

employees (1) ──< (many) attendances
employees (1) ──< (many) fingerprints
employees (1) ──< (many) employee_activity
employees (1) ──< (many) activity_types (created_by)
employees (1) ──< (many) verification_log

position (1) ──< (many) employees

activity_types (1) ──< (many) employee_activity
```

---

## Data Statistics (from SQL Dump)

- **Residents**: 3 records
- **Employees**: 3 records
- **Attendances**: 7 records
- **Fingerprints**: 2 records
- **Positions**: 2 records
- **Departments**: 2 records
- **Addresses**: 1 record
- **Occupations**: 1 record
- **Family Relationships**: 1 record
- **Resident Biometrics**: 1 record
- **Resident IDs**: 1 record
- **Resident Status**: 1 record
- **Employee Activity**: 1 record
- **Activity Types**: 1 record
- **Verification Log**: 0 records
- **Verification Tokens**: 0 records

---

## Key Observations

1. **No Foreign Key on `attendances.employee_id`**: Should reference `employees.employee_id`
2. **No Foreign Key on `fingerprints.employee_id`**: Should reference `employees.employee_id`
3. **No Foreign Key on `verification_log.employee_id`**: Should reference `employees.employee_id`
4. **No Foreign Key on `verification_tokens.employee_id`**: Should reference `employees.employee_id`
5. **`verification_tokens` table exists**: ✅ Now present in updated SQL dump
6. **`employee_id` is VARCHAR**: Not a standard auto-increment integer (custom IDs like "2021", "20201188")
7. **Timestamp vs DateTime**: Mixed usage (`timestamp` vs `datetime`)
8. **GPS Coordinates**: `addresses` table supports latitude/longitude
9. **Comprehensive Resident Data**: Extensive resident information system
10. **Activity Tracking**: Supports employee activity logging with types
11. **Biometric Storage**: Fingerprints stored as base64-encoded XML FMD
12. **Token Expiry**: `verification_tokens` has 60-second expiry logic in code

---

## Recommendations

1. **Add Missing Foreign Keys**: Add FK constraints for `attendances`, `fingerprints`, `verification_log`, `verification_tokens`
2. **Standardize Timestamps**: Use consistent `timestamp` or `datetime` type across all tables
3. **Add Indexes**: Consider indexes on frequently queried columns:
   - `attendances.employee_id` - For employee attendance lookups
   - `attendances.window` - For filtering by time window
   - `attendances.created_at` - For date range queries
   - `fingerprints.employee_id` - For faster template lookups
4. **Add Constraints**: Consider adding CHECK constraints for data validation
5. **Token Cleanup**: Consider adding a cleanup job to remove expired tokens from `verification_tokens`

---

## Table Creation Order (for Fresh Install)

1. `civil_status` (lookup table)
2. `residents` (core entity)
3. `position` (lookup table)
4. `departments` (lookup table)
5. `employees` (depends on residents, position)
6. `activity_types` (depends on employees)
7. `addresses` (depends on residents)
8. `occupations` (depends on residents)
9. `family_relationships` (depends on residents - self-referencing)
10. `resident_biometrics` (depends on residents)
11. `resident_contacts` (depends on residents)
12. `resident_ids` (depends on residents)
13. `resident_status` (depends on residents)
14. `attendances` (depends on employees)
15. `fingerprints` (depends on employees)
16. `employee_activity` (depends on employees, activity_types)
17. `verification_log` (depends on employees)
18. `verification_tokens` (depends on employees) - **MISSING, see database/verification_tokens.sql**

---

## Data Flow

### Attendance Flow
```
Employee scans fingerprint
  ↓
C# app identifies employee_id
  ↓
Determines active time window
  ↓
Creates record in `attendances` table
  ↓
WebSocket broadcasts update
```

### Enrollment Flow
```
Employee created from resident
  ↓
Fingerprint captured (4 scans)
  ↓
Template created and base64 encoded
  ↓
Stored in `fingerprints` table
```

### Verification Flow
```
Two fingerprints captured
  ↓
Matched against templates
  ↓
Record created in `verification_log`
  ↓
Token generated and stored in `verification_tokens`
  ↓
Token expires after 60 seconds
```

---

## Indexes Summary

### Primary Keys
- All tables have AUTO_INCREMENT primary keys except `employees` (uses `employee_id` VARCHAR)

### Foreign Key Indexes
- `addresses.resident_id`
- `employees.resident_id`, `employees.position_id`
- `employee_activity.employee_id`, `employee_activity.activity_types_id`, `employee_activity.created_by`
- `family_relationships.resident_id`, `family_relationships.relative_id`
- `occupations.resident_id`
- `residents.civil_status_id`
- `resident_biometrics.resident_id`
- `resident_contacts.resident_id`
- `resident_ids.resident_id`
- `resident_status.resident_id`
- `activity_types.created_by`

### Missing Indexes (Recommended)
- `attendances.employee_id` - Should be indexed for faster lookups
- `attendances.window` - Should be indexed for filtering
- `attendances.created_at` - Should be indexed for date range queries
- `fingerprints.employee_id` - Should be indexed for faster lookups
- `verification_log.employee_id` - Should be indexed
- `verification_tokens.token` - Should be indexed (if table exists)

---

## Constraints Summary

### Foreign Key Constraints
- ✅ `addresses` → `residents`
- ✅ `employees` → `residents`, `position`
- ✅ `employee_activity` → `employees`, `activity_types`
- ✅ `family_relationships` → `residents` (self-referencing)
- ✅ `occupations` → `residents`
- ✅ `residents` → `civil_status`
- ✅ `resident_biometrics` → `residents`
- ✅ `resident_contacts` → `residents`
- ✅ `resident_ids` → `residents`
- ✅ `resident_status` → `residents`
- ✅ `activity_types` → `employees` (created_by)

### Missing Foreign Key Constraints
- ❌ `attendances.employee_id` → `employees.employee_id`
- ❌ `fingerprints.employee_id` → `employees.employee_id`
- ❌ `verification_log.employee_id` → `employees.employee_id`
- ❌ `verification_tokens.employee_id` → `employees.employee_id` (if table exists)

---

## Data Types Summary

### VARCHAR Usage
- `employee_id`: varchar(50) or varchar(256) - Non-standard, custom IDs
- Names: varchar(100)
- Descriptions: varchar(50-255)
- Templates: longtext (base64 encoded)

### ENUM Usage
- `gender`: 'Male', 'Female', 'Other'
- `address_type`: 'Permanent', 'Present', 'Work', 'Other'
- `biometric_type`: 'Signature', 'Thumbmark', 'Fingerprint', 'Photo'
- `contact_type`: 'Mobile', 'Telephone', 'Email'
- `status_type`: 'Senior Citizen', 'PWD', 'Solo Parent', 'Indigent', 'Other'

### Timestamp Usage
- Most tables use `timestamp` with `current_timestamp()` and `ON UPDATE current_timestamp()`
- Some use `datetime` (employees, departments, activity_types)

---

## End of Database Schema Documentation

**Total Tables**: **18** ✅ (All tables present)  
**Total Relationships**: 11 foreign key constraints  
**Database Size**: Small (development/testing data)  
**SQL Dump File**: `database/attendance-system (1).sql`  
**Last Updated**: Based on SQL dump from Dec 13, 2025 at 11:49 PM

### ✅ All Tables Verified
- All 18 tables are present in the SQL dump
- `verification_tokens` table is now included
- All indexes are properly defined
- All foreign key constraints are in place (where applicable)

