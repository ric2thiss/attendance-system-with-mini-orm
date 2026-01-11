# Complete Codebase Scan - Attendance System

**Date**: Current Scan  
**Status**: ✅ Complete Analysis

---

## ⚠️ CRITICAL: C# Application Endpoints

**DO NOT MODIFY** any endpoints used by the C# application. See `C#_ENDPOINTS.md` for complete list.

---

## System Architecture

### Technology Stack
- **Backend**: PHP 7.4+ with PDO, MySQL
- **Desktop Client**: C# .NET Windows Forms with DPUruNet SDK
- **Frontend**: HTML5, Tailwind CSS, JavaScript, Bootstrap 5.3.8
- **Real-time**: Node.js WebSocket Server (ws://localhost:8080)
- **Biometric**: DigitalPersona U.are.U fingerprint SDK
- **Face Recognition**: face-api.js (resident interface)

---

## Directory Structure

```
attendance-system/
├── admin/                    # Admin dashboard pages
│   ├── assets/              # Images (employee photos, face recognition)
│   ├── js/                  # JavaScript files
│   ├── models/              # face-api.js models
│   ├── attendance.php       # Attendance logs page
│   ├── dashboard.php        # Main dashboard
│   ├── employees.php        # Employee management
│   ├── residents.php        # Resident management
│   ├── visitors.php         # Visitor tracking
│   ├── payroll.php          # Payroll management
│   ├── reports.html         # Reports page
│   └── settings.html        # Settings page
│
├── api/                     # REST API endpoints
│   ├── services.php         # Main API router (used by C#)
│   └── v1/
│       └── request.php      # Alternative API router
│
├── app/                     # MVC Application Core
│   ├── controller/          # Business logic controllers
│   ├── models/              # Data models (ORM)
│   ├── database/            # Database connection
│   └── query/               # Query builder
│
├── resident/                # Resident-facing interface
│   ├── index.html           # Face recognition attendance
│   ├── logbook.php          # Attendance logging endpoint
│   ├── script.js            # Face recognition logic
│   └── models/              # face-api.js models
│
├── shared/                  # Shared components
│   └── components/
│       ├── Sidebar.php      # Navigation sidebar
│       └── Sidebar.js       # Sidebar JavaScript
│
├── websocket/               # WebSocket server (moved from practice/)
│   ├── server.js            # Main server file
│   ├── package.json         # Dependencies
│   └── start.bat/.sh        # Startup scripts
│
├── utils/                   # Utilities
│   ├── styles/              # Global CSS
│   └── img/                 # Images
│
├── storage/                 # File storage
│   ├── img/                 # Uploaded images
│   └── logs/                # Log files
│
├── C# Application Files     # Desktop biometric client
│   ├── Program.cs           # Entry point
│   ├── Enrollment.cs        # Fingerprint enrollment
│   ├── Identification.cs    # Attendance logging
│   └── Verification.cs      # Two-finger verification
│
└── Root PHP Files           # Entry points
    ├── bootstrap.php        # Application bootstrap
    ├── config.php           # Configuration
    ├── autoloader.php       # Class autoloader
    ├── enroll.php           # Enrollment endpoint
    ├── biometricVerification.php  # Verification endpoint
    ├── verify.php           # Secure verification
    ├── biometric-success.php      # Success page
    └── attendance.php       # Simple attendance page
```

---

## API Endpoints

### api/services.php (Used by C# Application)

**Base URL**: `http://localhost/attendance-system/api/services.php`

1. **GET Templates**
   - URL: `?resource=templates`
   - Method: GET
   - Returns: JSON array of fingerprint templates
   - Used by: Identification.cs, Enrollment.cs, Verification.cs
   - **⚠️ DO NOT MODIFY**

2. **GET Attendance Windows**
   - URL: `?resource=attendance-windows`
   - Method: GET
   - Returns: JSON with windows array
   - Used by: Identification.cs
   - **⚠️ DO NOT MODIFY**

3. **GET Attendances**
   - URL: `?resource=attendances`
   - Method: GET
   - Returns: All attendance records
   - Used by: WebSocket server, admin dashboard

4. **POST Attendances**
   - URL: `?resource=attendances`
   - Method: POST
   - Body: `employee_id`, `window` (FormUrlEncoded or JSON)
   - Used by: Identification.cs
   - **⚠️ DO NOT MODIFY**

5. **GET Employees** (with API key)
   - URL: `?resource=employees`
   - Method: GET
   - Headers: `x-api-key: HELLOWORLD`
   - Returns: All employees with details

### api/v1/request.php (Alternative API)

**Base URL**: `http://localhost/attendance-system/api/v1/request.php`

1. **GET Residents**
   - URL: `?query=residents` or `?query=residents&id={id}`
   - Method: GET
   - Returns: Resident data

2. **GET/POST Employees**
   - URL: `?query=employees&filter=all`
   - Method: GET/POST
   - POST: Creates new employee

3. **GET Attendance**
   - URL: `?query=attendance&from={date}&to={date}`
   - URL: `?query=attendance&filter=all`
   - Method: GET
   - Returns: Attendance records

### Direct PHP Endpoints

1. **POST enroll.php**
   - Method: POST
   - Body: JSON `{employee_id, template}`
   - Used by: Enrollment.cs
   - **⚠️ DO NOT MODIFY**

2. **POST biometricVerification.php**
   - Method: POST
   - Body: JSON `{employee_id, status, device_id, timestamp}`
   - Used by: Verification.cs
   - **⚠️ DO NOT MODIFY**

3. **POST verify.php**
   - Method: POST
   - Body: JSON `{employee_id, status, token: "MY_SECRET_KEY"}`
   - Returns: Confirmation token
   - Used by: Verification.cs
   - **⚠️ DO NOT MODIFY**

4. **GET verify.php?confirm={token}**
   - Method: GET
   - Returns: HTML confirmation page
   - Used by: Verification.cs (browser redirect)
   - **⚠️ DO NOT MODIFY**

5. **GET biometric-success.php?employee_id={id}**
   - Method: GET
   - Returns: HTML success page
   - Used by: Enrollment.cs (browser redirect)
   - **⚠️ DO NOT MODIFY**

---

## Models (ORM)

### Base Model
- **Model.php**: Laravel-style ORM base class
  - Extends QueryBuilder
  - Provides: `all()`, `find()`, `create()`, `update()`, `save()`
  - Relationships: `hasOne()`, `hasMany()`, `belongsTo()`
  - Fillable protection

### Data Models

1. **Employee** (`app/models/Employee.php`)
   - Table: `employees`
   - Fillable: `employee_id`, `resident_id`, `position_id`, `hired_date`
   - Relationships: `attendances()` (hasMany)

2. **Attendance** (`app/models/Attendance.php`)
   - Table: `attendances`
   - Fillable: `employee_id`, `created_at`, `updated_at`, `window`
   - Relationships: `employee()` (belongsTo)

3. **Resident** (`app/models/Resident.php`)
   - Table: `residents`
   - Fillable: `phil_sys_number`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `birthdate`, `place_of_birth_city`, `place_of_birth_province`, `blood_type`, `civil_status_id`, `photo_path`

4. **Department** (`app/models/Department.php`)
   - Table: `departments`
   - Fillable: `department_id`, `department_name`

5. **Position** (`app/models/Position.php`)
   - Table: `position`
   - Fillable: `position_name`

6. **Fingerprints** (`app/models/Fingerprints.php`)
   - Table: `fingerprints`
   - Fillable: `employee_id`, `template`

7. **VerificationLog** (`app/models/VerificationLog.php`)
   - Table: `verification_log`
   - Fillable: `employee_id`, `status`, `device_id`, `ip_address`

8. **VerificationToken** (`app/models/VerificationToken.php`)
   - Table: `verification_tokens`
   - Fillable: `employee_id`, `status`, `token`

---

## Controllers

1. **AttendanceController**
   - `index()`: Get all attendances with last attendee info
   - `store($data)`: Create attendance record
   - `windows()`: Get attendance time windows
   - `getAttendanceBetween($from, $to)`: Get attendance in date range
   - `getAttendanceCountToday()`: Count unique employees today

2. **EmployeeController**
   - `store($data)`: Create employee from resident
   - `getAllEmployees()`: Get all employees with joins

3. **ResidentController**
   - `getAllResidents($id)`: Get resident(s) with complex joins
   - `getAllResidentNotEmployee()`: Get residents not yet employees

4. **FingerprintsController**
   - `index()`: Get all fingerprint templates
   - `enroll($data)`: Enroll new fingerprint

5. **BiometricController**
   - `store($data)`: Handle biometric verification

6. **VerificationLogController**
   - `store($data)`: Create verification log and token
   - `confirm($token)`: Confirm verification token

7. **DepartmentController**
   - `getDepartmentLists()`: Get all departments

8. **PositionController**
   - `getAllPosition()`: Get all positions

---

## Database Schema (Inferred)

### Core Tables
- `employees` - Employee records
- `attendances` - Attendance logs
- `residents` - Resident information
- `departments` - Department data
- `position` - Job positions
- `fingerprints` - Biometric templates (base64 encoded XML FMD)

### Verification Tables
- `verification_log` - Verification tracking
- `verification_tokens` - Token management (1-minute expiry)

### Related Tables (from ResidentController joins)
- `occupations` - Job information
- `addresses` - Address data
- `family_relationships` - Family connections
- `resident_biometrics` - Resident biometrics
- `resident_contacts` - Contact information
- `resident_ids` - ID documents
- `resident_status` - Status information
- `civil_status` - Civil status lookup
- `employee_activity` - Employee activity tracking
- `activity_types` - Activity type definitions

---

## C# Desktop Application

### Files
- **Program.cs**: Entry point, handles `biometrics://` protocol
- **Enrollment.cs**: Fingerprint enrollment (4 scans)
- **Identification.cs**: Real-time attendance logging
- **Verification.cs**: Two-finger verification

### Protocol Handler
- `biometrics://enroll?employee_id={id}` - Launch enrollment
- `biometrics://identify` - Launch identification
- `biometrics://verify` - Launch verification

### Endpoints Used (DO NOT MODIFY)
See `C#_ENDPOINTS.md` for complete list.

---

## WebSocket Server

**Location**: `websocket/server.js`  
**Port**: 8080  
**URL**: `ws://localhost:8080`

### Functionality
- Broadcasts attendance data to all connected clients
- Fetches data from: `http://localhost/attendance-system/api/services.php?resource=attendances`
- Tracks client connections/disconnections

### Connected Clients
- `admin/dashboard.php` - Real-time dashboard updates
- `admin/attendance.php` - Real-time attendance logs
- `Identification.cs` - C# biometric client

---

## Frontend Pages

### Admin Dashboard (`admin/`)
- **dashboard.php**: Main dashboard with metrics, charts, real-time clock
- **attendance.php**: Attendance logs with WebSocket updates
- **employees.php**: Employee directory with CRUD modals
- **residents.php**: Resident management
- **visitors.php**: Visitor tracking
- **payroll.php**: Payroll management
- **reports.html**: Reports page
- **settings.html**: Settings page

### Resident Interface (`resident/`)
- **index.html**: Face recognition attendance using face-api.js
- **logbook.php**: Attendance logging endpoint

---

## Configuration

### Database (`app/database/Database.php`)
- Host: `localhost`
- Database: `attendance-system`
- User: `root`
- Password: `` (empty)

### Application (`bootstrap.php`)
- Timezone: `Asia/Manila`
- API Key: `HELLOWORLD` (hardcoded)

### Base Path (`config.php`)
- `BASE_PATH`: `__DIR__ . "/"`

---

## Attendance Windows

Defined in `AttendanceController::getWindows()`:
1. **morning_in**: 06:00:00 - 11:59:00
2. **morning_out**: 12:00:00 - 12:59:00
3. **afternoon_in**: 13:00:00 - 15:59:00
4. **afternoon_out**: 16:00:00 - 17:30:00

---

## Security Features

1. **API Key Authentication**
   - Some endpoints require `x-api-key: HELLOWORLD` header
   - Defined in `bootstrap.php`

2. **Token-based Verification**
   - Secret key: `MY_SECRET_KEY` (hardcoded in verify.php)
   - Token expiry: 60 seconds
   - Used for secure verification flow

3. **Duplicate Prevention**
   - Attendance: One log per window per day
   - Fingerprint: Duplicate detection before enrollment

4. **Fillable Protection**
   - Models use `$fillable` array to prevent mass assignment
   - Only specified fields can be set via `create()` or `update()`

---

## Integration Flow

### Enrollment Flow
```
Browser → biometrics://enroll?employee_id=123
  ↓
C# Enrollment form opens
  ↓
Captures 4 fingerprints
  ↓
Checks for duplicates
  ↓
POST to enroll.php
  ↓
Opens biometric-success.php in browser
```

### Identification Flow
```
Browser → biometrics://identify
  ↓
C# Identification form opens
  ↓
Captures fingerprint
  ↓
GET templates from API
  ↓
Compares against all templates
  ↓
Determines active time window
  ↓
POST attendance to API
  ↓
Connects to WebSocket
  ↓
Shows success message
```

### Verification Flow
```
Browser → biometrics://verify
  ↓
C# Verification form opens
  ↓
Captures first finger
  ↓
Captures second finger
  ↓
Compares against templates
  ↓
POST to verify.php (with secret token)
  ↓
Receives confirmation token
  ↓
Opens browser with confirmation URL
```

---

## Key Features

1. ✅ Multi-window attendance system
2. ✅ Biometric fingerprint authentication
3. ✅ Face recognition (resident interface)
4. ✅ Real-time WebSocket updates
5. ✅ Employee management from residents
6. ✅ Complex resident data with relationships
7. ✅ Token-based secure verification
8. ✅ Duplicate prevention
9. ✅ Custom protocol handler (`biometrics://`)
10. ✅ Laravel-style ORM

---

## Dependencies

### PHP
- PDO (MySQL)
- Standard PHP libraries

### Node.js (WebSocket)
- `ws`: ^8.18.3
- `fetch`: ^1.1.0
- `node-fetch`: ^3.3.2

### Frontend
- Bootstrap 5.3.8
- Tailwind CSS (CDN)
- Chart.js 4.4.3
- face-api.js 0.22.2

### C# (.NET)
- DPUruNet (DigitalPersona SDK)
- Newtonsoft.Json
- System.Net.Http
- System.Net.WebSockets

---

## Notes

- All C# endpoints are documented in `C#_ENDPOINTS.md`
- WebSocket server moved from `practice/nodejs/7-websockets-1/` to `websocket/`
- Practice folder contains development/testing files
- Some endpoints have commented Laravel alternatives
- API key is hardcoded (consider environment variables for production)
- Secret token is hardcoded (consider secure storage for production)

---

## End of Scan

**Total Files Scanned**: Complete codebase  
**C# Files**: 4 (Program.cs, Enrollment.cs, Identification.cs, Verification.cs)  
**PHP Files**: 40+  
**JavaScript Files**: Multiple  
**Models**: 8  
**Controllers**: 8  
**API Endpoints**: 10+  

**Status**: ✅ Complete

