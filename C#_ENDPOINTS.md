# C# Application Endpoints - DO NOT MODIFY

## ⚠️ IMPORTANT: These endpoints are used by the C# application. DO NOT CHANGE them.

### Identification.cs Endpoints

1. **GET Templates**
   - URL: `http://localhost/attendance-system/api/services.php?resource=templates`
   - Line: 69
   - Purpose: Fetch all fingerprint templates for comparison
   - Returns: JSON array with employee_id and template (base64)

2. **GET Attendance Windows**
   - URL: `http://localhost/attendance-system/api/services.php?resource=attendance-windows`
   - Line: 99
   - Purpose: Get available time windows (morning_in, morning_out, etc.)
   - Returns: JSON with windows array

3. **POST Attendance Log**
   - URL: `http://localhost/attendance-system/api/services.php?resource=attendances`
   - Line: 130
   - Method: POST
   - Body: FormUrlEncodedContent with `employee_id` and `window`
   - Purpose: Log attendance for matched employee

4. **WebSocket Connection**
   - URL: `ws://localhost:8080`
   - Line: 189
   - Purpose: Connect to WebSocket server for real-time updates

5. **GET Admin Attendance Page** (Commented but present)
   - URL: `http://localhost/attendance-system/admin/attendance.php`
   - Line: 139
   - Purpose: Fetch attendance page (currently commented out)

---

### Enrollment.cs Endpoints

1. **POST Enrollment**
   - URL: `http://localhost/attendance-system/enroll.php`
   - Line: 962
   - Method: POST
   - Body: JSON with `employee_id` and `template` (base64)
   - Purpose: Enroll new fingerprint template

2. **GET Templates (Duplicate Check)**
   - URL: `http://localhost/attendance-system/api/services.php?resource=templates`
   - Line: 1065
   - Purpose: Check if fingerprint already exists before enrollment

3. **Browser Redirect (Success Page)**
   - URL: `http://localhost/attendance-system/biometric-success.php?employee_id={id}`
   - Line: 967
   - Purpose: Open success page in browser after enrollment

---

### Verification.cs Endpoints

1. **GET Templates**
   - URL: `http://localhost/attendance-system/api/services.php?resource=templates`
   - Line: 43
   - Purpose: Fetch all templates for verification

2. **POST Biometric Verification**
   - URL: `http://localhost/attendance-system/biometricVerification.php`
   - Line: 89
   - Method: POST
   - Body: JSON with `employee_id`, `status`, `device_id`, `timestamp`
   - Purpose: Send verification result

3. **POST Secure Verification**
   - URL: `http://localhost/attendance-system/verify.php`
   - Line: 153
   - Method: POST
   - Body: JSON with `employee_id`, `status`, `token` (MY_SECRET_KEY)
   - Purpose: Secure verification with token, returns confirmation token

4. **GET Verification Confirmation**
   - URL: `http://localhost/attendance-system/verify.php?confirm={token}`
   - Line: 160
   - Purpose: Open browser confirmation page with token

---

## Summary of All Endpoints Used

### API Endpoints (api/services.php)
- `GET ?resource=templates` - Used by: Identification, Enrollment, Verification
- `GET ?resource=attendance-windows` - Used by: Identification
- `POST ?resource=attendances` - Used by: Identification

### Direct PHP Endpoints
- `POST enroll.php` - Used by: Enrollment
- `POST biometricVerification.php` - Used by: Verification
- `POST verify.php` - Used by: Verification
- `GET verify.php?confirm={token}` - Used by: Verification (browser)
- `GET biometric-success.php?employee_id={id}` - Used by: Enrollment (browser)

### WebSocket
- `ws://localhost:8080` - Used by: Identification

---

## Notes
- All endpoints use `http://localhost/attendance-system/` as base URL
- WebSocket uses `ws://localhost:8080`
- No API key authentication required for these endpoints (as per C# code)
- Some endpoints have commented Laravel alternatives but PHP endpoints are active

