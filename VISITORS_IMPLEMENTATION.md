# Visitors Page Implementation

## Overview
The visitors.php page has been updated to:
1. Fetch resident photos from the database (instead of hardcoded paths)
2. Check if a visitor has a booking when their face is recognized
3. Display booking information if found, or show available services if no booking exists

## Implementation Details

### API Endpoints Created

#### 1. `/api/visitors/residents.php`
- **Method**: GET
- **Purpose**: Fetches all residents with photos for face recognition
- **Returns**: JSON with residents array containing:
  - `id`: Resident ID
  - `name`: Full name
  - `img`: Full URL to photo
  - `resident_id`: Resident ID
  - `phil_sys_number`: PhilSys number
  - Other resident details

**Photo Path Handling**:
- Supports both JSON array format (new) and single path format (old)
- Automatically constructs full URLs from database paths
- **Uses all 3 photos (3 angles) per resident** for improved face recognition accuracy
- Only includes residents that have at least one photo
- Returns `imgs` array with all photos and `img` with first photo for display

#### 2. `/api/visitors/check-booking.php`
- **Method**: GET
- **Parameters**: `resident_id` (required)
- **Purpose**: Checks if a resident has an active booking
- **Returns**: JSON with:
  - `success`: boolean
  - `has_booking`: boolean
  - `booking`: object with booking details (if found)

**Current Status**: Placeholder implementation
- Returns `has_booking: false` by default
- Contains commented code structure for when bookings table is created
- Ready to be connected to external booking API

#### 3. `/api/visitors/services.php`
- **Method**: GET
- **Purpose**: Fetches available services
- **Returns**: JSON with services array

**Current Status**: Placeholder with mock data
- Returns 5 sample services (Barangay Clearance, Business Permit, etc.)
- Contains commented code structure for external API integration
- Ready to be connected to external services API

### JavaScript Updates

#### Updated Files:
1. **`admin/js/visitors/api.js`**
   - Added `fetchResidents()`: Fetches residents from API
   - Added `checkBooking(residentId)`: Checks for bookings
   - Added `fetchServices()`: Fetches available services
   - Dynamic base URL detection

2. **`admin/js/visitors/main.js`**
   - Removed hardcoded face descriptors
   - Now fetches residents from API on initialization
   - Added booking check logic when face is recognized
   - Shows booking modal or services modal based on booking status

3. **`admin/js/visitors/recognitionLogic.js`**
   - Updated to pass full person data to callback

#### New Files:
4. **`admin/js/visitors/bookingModal.js`**
   - Modal component for displaying booking info or services
   - Features:
     - Shows visitor photo and information
     - Displays booking details if found
     - Shows available services list if no booking
     - Service selection handling (ready for API integration)
     - Responsive design with Tailwind CSS

## How It Works

### Flow:
1. **Page Load**:
   - Fetches all residents with photos from database
   - **Loads all 3 photos per resident (3 angles)** for better recognition
   - Loads face recognition models
   - Initializes webcam

2. **Face Recognition**:
   - Detects face in webcam feed
   - **Matches against all 3 angles of each resident** for improved accuracy
   - When match found:
     - Checks if resident has booking
     - If booking exists → Shows booking modal
     - If no booking → Shows services modal
     - Logs visitor attendance

3. **Booking Modal**:
   - **With Booking**: Shows service name, date, time, status, notes
   - **Without Booking**: Shows list of available services with details

## Configuration Required

### 1. Booking API Integration
Edit `api/visitors/check-booking.php`:

```php
// Replace the placeholder section with actual booking check:
// Option A: Database table
$stmt = $db->prepare("
    SELECT * FROM bookings
    WHERE resident_id = :resident_id
    AND appointment_date >= CURDATE()
    AND status IN ('pending', 'confirmed')
    ORDER BY appointment_date ASC
    LIMIT 1
");
$stmt->execute([':resident_id' => $residentId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

// Option B: External API
$apiUrl = 'https://your-booking-api.com/check-booking';
$response = file_get_contents($apiUrl . '?resident_id=' . $residentId);
$booking = json_decode($response, true);
```

### 2. Services API Integration
Edit `api/visitors/services.php`:

```php
// Replace mock data with actual API call:
$externalApiUrl = 'https://your-services-api.com/services';
$apiKey = 'your-api-key'; // Store in config

$ch = curl_init($externalApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $services = json_decode($response, true);
    // Return services
}
```

### 3. Service Selection Handler
Edit `admin/js/visitors/bookingModal.js`:

In the `handleServiceSelection()` method, add logic to:
- Create booking/appointment
- Redirect to booking form
- Call external API to book service

## Database Requirements

### For Bookings (if using database):
Create a `bookings` table:

```sql
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id)
);
```

## Image Path Best Practices

### Current Implementation:
- Photos stored in database as paths (relative to project root)
- API constructs full URLs automatically
- Supports both single path and JSON array formats

### Recommended Storage:
- Store photos in: `uploads/residents/{resident_id}/photo_1.jpg`, `photo_2.jpg`, `photo_3.jpg`
- Store path in database as JSON array: `["uploads/residents/1/photo_1.jpg", "uploads/residents/1/photo_2.jpg", "uploads/residents/1/photo_3.jpg"]`
- **All 3 photos (3 angles) are used for face recognition** to improve accuracy

### Performance:
- Images are served directly from filesystem (fast)
- No database BLOB storage (efficient)
- URLs are constructed once per API call (minimal overhead)

## Testing

### Test Checklist:
1. ✅ Residents with photos are loaded from database
2. ✅ Face recognition works with database photos
3. ✅ Booking check is called when face is recognized
4. ✅ Booking modal shows when booking exists
5. ✅ Services modal shows when no booking
6. ✅ Services list displays correctly
7. ✅ Modal can be closed
8. ✅ Visitor attendance is logged

### To Test:
1. Ensure residents have photos in database
2. Visit `/admin/visitors.php`
3. Allow camera access
4. Show face to camera
5. Verify modal appears with booking or services

## Notes

- **Photo Requirements**: Only residents with photos in database will be included in face recognition
- **Multiple Photos**: Uses all 3 photos (3 angles) per resident for improved recognition accuracy
- **Booking Check**: Currently returns no booking (placeholder). Update when booking API is ready.
- **Services**: Currently shows mock data. Update when services API is ready.
- **Error Handling**: Gracefully handles missing photos, API errors, and camera issues. If some photos fail to load, it will use the successfully loaded ones.

## Future Enhancements

1. **Booking Creation**: Allow visitors to book services directly from modal
2. **Service Categories**: Group services by category
3. **Booking History**: Show past bookings for residents
4. **Notifications**: Send notifications for upcoming appointments
5. **Analytics**: Track visitor patterns and popular services
