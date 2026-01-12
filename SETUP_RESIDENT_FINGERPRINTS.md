# Resident Fingerprints Setup Instructions

## Step 1: Create Database Table

Run the SQL migration file to create the `resident_fingerprints` table.

### Option A: Using phpMyAdmin (Recommended)
1. Open phpMyAdmin in your browser: `http://localhost/phpmyadmin`
2. Select the `attendance-system` database
3. Click on the "SQL" tab
4. Copy and paste the contents of `database/resident_fingerprints.sql`
5. Click "Go" to execute

### Option B: Using MySQL Command Line
```bash
mysql -u root -p attendance-system < database/resident_fingerprints.sql
```

### Option C: Copy SQL Content
If you prefer, here's the SQL content:

```sql
CREATE TABLE `resident_fingerprints` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `template` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `resident_fingerprints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

ALTER TABLE `resident_fingerprints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `resident_fingerprints`
  ADD CONSTRAINT `resident_fingerprints_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;
```

## Step 2: Update C# Application (Future)

**Note:** The C# application currently only supports `employee_id`. To support resident fingerprint enrollment, the C# application needs to be updated to:

1. **Program.cs**: Accept `resident_id` parameter from URL (in addition to `employee_id`)
2. **Enrollment.cs**: 
   - Accept `resident_id` parameter in constructor
   - Send enrollment to `resident-enroll.php` when `resident_id` is provided
   - Update endpoint from `enroll.php` to `resident-enroll.php`
   - Update parameter from `employee_id` to `resident_id` in JSON payload

**Current Implementation (for reference):**
- Employee enrollment: `biometrics://enroll?employee_id=123` → sends to `enroll.php`
- Resident enrollment (needed): `biometrics://enroll?resident_id=123` → should send to `resident-enroll.php`

## Step 3: Test the Implementation

Once the database table is created:

1. **Create a new resident** via the admin panel
2. **Edit the resident** and go to the "Photos & Biometrics" tab
3. **Upload 3 photos** of the resident
4. **Click "Register Fingerprint"** button (currently uses `biometrics://enroll?resident_id=X`)
5. The C# application should open (once updated to support `resident_id`)
6. Complete the fingerprint enrollment process

## Files Created/Modified

### New Files:
- `database/resident_fingerprints.sql` - Database migration
- `app/models/ResidentFingerprints.php` - Model
- `app/repositories/ResidentFingerprintsRepository.php` - Repository
- `app/controller/ResidentFingerprintsController.php` - Controller
- `resident-enroll.php` - Enrollment endpoint

### Modified Files:
- `admin/residents/create.php` - Updated Photos & Biometrics section
- `admin/residents/edit.php` - Updated Photos & Biometrics section

## API Endpoint

**POST** `/resident-enroll.php`

Request Body:
```json
{
  "resident_id": 123,
  "template": "base64_encoded_fingerprint_template"
}
```

Response (Success - 201):
```json
{
  "message": "Fingerprint enrolled successfully"
}
```

Response (Already Enrolled - 409):
```json
{
  "message": "Resident already enrolled"
}
```

Response (Error - 422):
```json
{
  "success": false,
  "error": "Resident ID is required"
}
```

## Database Schema

The `resident_fingerprints` table structure matches the `fingerprints` table:

| Column | Type | Description |
|--------|------|-------------|
| id | int(11) | Primary key (auto-increment) |
| resident_id | int(11) | Foreign key to residents table |
| template | longtext | Base64 encoded XML FMD fingerprint template |
| created_at | timestamp | Creation timestamp |
| updated_at | datetime | Last update timestamp |
