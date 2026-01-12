# Visitor Logging Implementation - Complete Guide

## Overview
This document describes the complete implementation of the visitor logging system with support for all three visitor scenarios.

## Database Migration

### Step 1: Run Migration
Execute the migration script to create the `visitor_logs` table:

```bash
mysql -u root -p attendance-system < database/migrations/create_visitor_logs_table.sql
```

Or import via phpMyAdmin:
1. Open phpMyAdmin
2. Select `attendance-system` database
3. Go to Import tab
4. Select `database/migrations/create_visitor_logs_table.sql`
5. Click Go

## Implementation Summary

### Files Created

1. **Database**
   - `database/migrations/create_visitor_logs_table.sql` - Migration script

2. **Models**
   - `app/models/VisitorLog.php` - VisitorLog model

3. **Repositories**
   - `app/repositories/VisitorLogRepository.php` - Visitor log repository

4. **Controllers**
   - `app/controller/VisitorLogController.php` - Visitor log controller

5. **API Endpoints**
   - `api/visitors/log.php` - Log visitor attendance
   - `api/visitors/resident-address.php` - Get resident address

6. **JavaScript Updates**
   - `admin/js/visitors/api.js` - Updated with new logging methods
   - `admin/js/visitors/main.js` - Updated with scenario handling
   - `admin/js/visitors/bookingModal.js` - Updated with service selection callback

## Visitor Scenarios Implementation

### Scenario 1: Resident with Booking

**Flow**:
1. Face recognition matches resident
2. System checks for booking
3. If booking found:
   - Fetches resident address
   - Automatically logs visitor entry
   - Shows booking information modal

**Code Location**: `admin/js/visitors/main.js` - `logResidentWithBooking()`

**Data Logged**:
```javascript
{
    resident_id: 123,
    first_name: "John",
    middle_name: "Doe",
    last_name: "Smith",
    address: "D5, Ave Street, Brgy. ampayon, Butuan City, Agusan Del Norte",
    purpose: "Barangay Clearance",
    is_resident: true,
    had_booking: true,
    booking_id: "BOOK-12345"
}
```

### Scenario 2: Resident without Booking

**Flow**:
1. Face recognition matches resident
2. System checks for booking
3. If no booking found:
   - Shows services modal
   - User selects service
   - Fetches resident address
   - Logs visitor entry
   - Submits service application to external API (if configured)

**Code Location**: `admin/js/visitors/main.js` - `logResidentWithoutBooking()`

**Data Logged**:
```javascript
{
    resident_id: 123,
    first_name: "John",
    middle_name: "Doe",
    last_name: "Smith",
    address: "D5, Ave Street, Brgy. ampayon, Butuan City, Agusan Del Norte",
    purpose: "Business Permit",
    is_resident: true,
    had_booking: false
}
```

### Scenario 3: Non-Resident Visitor

**Flow**:
1. Face recognition runs for 5 seconds
2. If no match found:
   - Shows visitor information form
   - User fills: first_name, middle_name, last_name, birthdate, address
   - Shows service selection
   - User selects service
   - Logs visitor entry
   - Submits service application to external API

**Code Location**: `admin/js/visitors/main.js` - (To be implemented for non-resident form)

**Data Logged**:
```javascript
{
    resident_id: null,
    first_name: "Jane",
    middle_name: null,
    last_name: "Doe",
    birthdate: "1990-01-01",
    address: "123 Main St, Brgy. Example, City, Province",
    purpose: "Indigency Certificate",
    is_resident: false,
    had_booking: false
}
```

## API Endpoints

### POST `/api/visitors/log.php`
Log visitor attendance.

**Request Body**:
```json
{
    "resident_id": 123,
    "first_name": "John",
    "middle_name": "Doe",
    "last_name": "Smith",
    "address": "Full address string",
    "purpose": "Service name",
    "is_resident": true,
    "had_booking": true,
    "booking_id": "BOOK-12345",
    "birthdate": "1990-01-01"
}
```

**Response**:
```json
{
    "success": true,
    "message": "Visitor log created successfully",
    "data": {
        "id": 1,
        "resident_id": 123,
        "first_name": "John",
        ...
    }
}
```

### GET `/api/visitors/resident-address.php?resident_id={id}`
Get formatted address for a resident.

**Response**:
```json
{
    "success": true,
    "address": "D5, Ave Street, Brgy. ampayon, Butuan City, Agusan Del Norte",
    "address_parts": {
        "house_number": "D5",
        "street_name": "Ave Street",
        ...
    }
}
```

## External API Integration

### Service Application Submission

When a service is selected, the system will:
1. Log visitor entry locally (only visitor data, not service application data)
2. Submit service application to external API (if configured)

**Example External API Call**:
```javascript
// Log payload to console (for debugging)
console.log('External API Payload:', JSON.stringify(servicePayload, null, 2));

// Submit to external API
await visitorAPI.submitServiceApplication(servicePayload, externalApiUrl);
```

**Important**: Service application data is NOT stored locally. Only the service name (`purpose`) is stored in `visitor_logs`.

## Testing Checklist

### Scenario 1: Resident with Booking
- [ ] Face recognition matches resident
- [ ] Booking check returns booking
- [ ] Visitor log created automatically
- [ ] Booking modal shows correct information
- [ ] Address fetched correctly
- [ ] All required fields logged

### Scenario 2: Resident without Booking
- [ ] Face recognition matches resident
- [ ] Booking check returns no booking
- [ ] Services modal displayed
- [ ] Service selection works
- [ ] Visitor log created after service selection
- [ ] External API called (if configured)
- [ ] Address fetched correctly

### Scenario 3: Non-Resident Visitor
- [ ] No face match after 5 seconds
- [ ] Visitor form displayed
- [ ] Form validation works
- [ ] Service selection works
- [ ] Visitor log created with is_resident=false
- [ ] Birthdate required and validated
- [ ] External API called (if configured)

## Next Steps

1. **Implement Non-Resident Form**: Create form UI for non-resident visitors (Scenario 3)
2. **Add 5-Second Timeout**: Implement timeout logic for face recognition
3. **External API Configuration**: Add configuration for external API URLs
4. **Update Statistics**: Update visitor statistics endpoints to use `visitor_logs`
5. **Add Visitor Logs View**: Create admin page to view visitor logs

## Notes

- All visitor logs are stored in `visitor_logs` table
- Service application data is NOT stored locally (only sent to external API)
- Console logging is implemented for debugging external API payloads
- Address is stored as formatted string for flexibility
- `verification_log` table remains unchanged for C# application compatibility
