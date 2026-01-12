# Visitor Attendance & Logging Flow - Database Schema Analysis

## Executive Summary

**Current State**: The system uses `verification_log` table for visitor tracking, but it's designed for employee verification and lacks required fields for proper visitor logging.

**Recommendation**: Create a new `visitor_logs` table specifically for visitor attendance tracking that supports all three visitor scenarios.

---

## Current Database Schema Analysis

### Existing Tables Used for Visitors

#### 1. `verification_log` Table (Currently Used)
```sql
CREATE TABLE `verification_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(255) NOT NULL,  -- ❌ PROBLEM: Required, but visitors may not be employees
  `status` varchar(255) NOT NULL,        -- Used for online/appointment tracking
  `device_id` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
)
```

**Issues**:
- ❌ `employee_id` is REQUIRED but visitors may not be employees
- ❌ Missing `resident_id` field (nullable for non-residents)
- ❌ Missing `first_name`, `middle_name`, `last_name` fields
- ❌ Missing `address` field
- ❌ Missing `purpose` field (service name)
- ❌ Missing `birthdate` field (for non-residents)
- ❌ Missing `is_resident` flag
- ❌ `device_id` and `ip_address` not needed for visitor logging

**Current Usage**: 
- Used in `api/visitors/stats.php` for visitor statistics
- Used in `api/visitors/chart.php` for visitor charts
- Repurposed from employee verification system

#### 2. `residents` Table (Available)
```sql
CREATE TABLE `residents` (
  `resident_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birthdate` date NOT NULL,
  -- ... other fields
)
```
✅ Can be referenced for resident visitors

#### 3. `addresses` Table (Available)
```sql
CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `municipality_city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  -- ... other address fields
)
```
✅ Can be referenced for resident addresses

---

## Required Data Fields Analysis

### Scenario 1: Resident with Online Booking
**Required Fields**:
- ✅ `resident_id` (from residents table)
- ✅ `first_name` (from residents table)
- ✅ `middle_name` (from residents table, nullable)
- ✅ `last_name` (from residents table)
- ✅ `address` (from addresses table - needs to be formatted/stored)
- ✅ `purpose` (from booking - service name)
- ✅ `timestamp` (auto-generated)
- ✅ `is_resident` = true

**Data Source**: 
- Resident data from `residents` table
- Address from `addresses` table
- Purpose from booking API

### Scenario 2: Resident without Booking
**Required Fields**:
- ✅ `resident_id` (from residents table)
- ✅ `first_name` (from residents table)
- ✅ `middle_name` (from residents table, nullable)
- ✅ `last_name` (from residents table)
- ✅ `address` (from addresses table)
- ✅ `purpose` (from selected service)
- ✅ `timestamp` (auto-generated)
- ✅ `is_resident` = true

**Data Source**: 
- Resident data from `residents` table
- Address from `addresses` table
- Purpose from service selection

### Scenario 3: Non-Resident Visitor
**Required Fields**:
- ❌ `resident_id` = NULL (not a resident)
- ✅ `first_name` (from form input)
- ✅ `middle_name` (from form input, nullable)
- ✅ `last_name` (from form input)
- ✅ `birthdate` (from form input)
- ✅ `address` (from form input)
- ✅ `purpose` (from selected service)
- ✅ `timestamp` (auto-generated)
- ✅ `is_resident` = false

**Data Source**: 
- All data from form input (not in database)

---

## Proposed Database Schema

### New Table: `visitor_logs`

```sql
CREATE TABLE `visitor_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  
  -- Resident Reference (nullable for non-residents)
  `resident_id` int(11) DEFAULT NULL,
  
  -- Visitor Identity (required for all visitors)
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,  -- Required for non-residents, optional for residents
  
  -- Address (stored as formatted string for flexibility)
  `address` text NOT NULL,  -- Full address string (barangay, city, province, etc.)
  
  -- Purpose/Service
  `purpose` varchar(255) NOT NULL,  -- Service name or purpose of visit
  
  -- Visitor Type Flag
  `is_resident` tinyint(1) NOT NULL DEFAULT 0,  -- 1 = resident, 0 = non-resident
  
  -- Booking Reference (optional - for tracking if visitor had booking)
  `had_booking` tinyint(1) DEFAULT 0,  -- 1 = had booking, 0 = walk-in
  `booking_id` varchar(255) DEFAULT NULL,  -- External booking ID if available
  
  -- Timestamps
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  
  PRIMARY KEY (`id`),
  KEY `idx_resident_id` (`resident_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_resident` (`is_resident`),
  KEY `idx_purpose` (`purpose`),
  
  CONSTRAINT `fk_visitor_logs_resident` 
    FOREIGN KEY (`resident_id`) 
    REFERENCES `residents` (`resident_id`) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Field Justifications

| Field | Type | Nullable | Justification |
|-------|------|----------|---------------|
| `id` | bigint(20) | NO | Primary key, auto-increment |
| `resident_id` | int(11) | YES | Links to residents table if visitor is a resident. NULL for non-residents |
| `first_name` | varchar(100) | NO | Required for all visitors |
| `middle_name` | varchar(100) | YES | Optional middle name |
| `last_name` | varchar(100) | NO | Required for all visitors |
| `birthdate` | date | YES | Required for non-residents, optional for residents (can get from residents table) |
| `address` | text | NO | Full address string - flexible format to handle both resident and non-resident addresses |
| `purpose` | varchar(255) | NO | Service name or purpose of visit |
| `is_resident` | tinyint(1) | NO | Flag to quickly identify resident vs non-resident visitors |
| `had_booking` | tinyint(1) | YES | Track if visitor had online booking |
| `booking_id` | varchar(255) | YES | External booking ID for reference (not stored locally per requirements) |
| `created_at` | timestamp | NO | Auto-generated timestamp |
| `updated_at` | timestamp | YES | Auto-updated timestamp |

### Indexes Justification

1. **`idx_resident_id`**: Fast lookups for resident visitors
2. **`idx_created_at`**: Fast date range queries for statistics
3. **`idx_is_resident`**: Fast filtering by visitor type
4. **`idx_purpose`**: Fast filtering by service/purpose

### Foreign Key Justification

- **`fk_visitor_logs_resident`**: 
  - Links to `residents` table
  - `ON DELETE SET NULL`: If resident is deleted, keep the log but set resident_id to NULL
  - `ON UPDATE CASCADE`: If resident_id changes, update the log

---

## Migration Strategy

### Option 1: Create New Table (Recommended)
**Pros**:
- Clean separation of concerns
- Proper schema for visitor logging
- No impact on existing `verification_log` table
- Can migrate existing data if needed

**Cons**:
- Need to update existing code that uses `verification_log` for visitors
- Need to update statistics/charts endpoints

### Option 2: Modify `verification_log` Table
**Pros**:
- No new table needed
- Existing code might work with modifications

**Cons**:
- ❌ Breaks existing employee verification functionality
- ❌ `employee_id` is required but visitors may not be employees
- ❌ Missing required fields
- ❌ Violates C# application constraints (DO NOT MODIFY)

**Recommendation**: **Option 1** - Create new `visitor_logs` table

---

## Data Flow Implementation

### Scenario 1: Resident with Booking
```php
// After face recognition match + booking check
INSERT INTO visitor_logs (
    resident_id,
    first_name,
    middle_name,
    last_name,
    address,
    purpose,
    is_resident,
    had_booking,
    booking_id
) VALUES (
    :resident_id,           // From residents table
    :first_name,            // From residents table
    :middle_name,           // From residents table (nullable)
    :last_name,             // From residents table
    :address,               // Formatted from addresses table
    :purpose,               // From booking.service_name
    1,                      // is_resident = true
    1,                      // had_booking = true
    :booking_id            // From booking.booking_id
)
```

### Scenario 2: Resident without Booking
```php
// After face recognition match + service selection
INSERT INTO visitor_logs (
    resident_id,
    first_name,
    middle_name,
    last_name,
    address,
    purpose,
    is_resident,
    had_booking
) VALUES (
    :resident_id,           // From residents table
    :first_name,            // From residents table
    :middle_name,           // From residents table (nullable)
    :last_name,             // From residents table
    :address,               // Formatted from addresses table
    :purpose,               // From selected service
    1,                      // is_resident = true
    0                       // had_booking = false
)
```

### Scenario 3: Non-Resident Visitor
```php
// After form submission (no face match after 5 seconds)
INSERT INTO visitor_logs (
    resident_id,
    first_name,
    middle_name,
    last_name,
    birthdate,
    address,
    purpose,
    is_resident,
    had_booking
) VALUES (
    NULL,                   // Not a resident
    :first_name,            // From form
    :middle_name,           // From form (nullable)
    :last_name,             // From form
    :birthdate,             // From form
    :address,               // From form
    :purpose,               // From selected service
    0,                      // is_resident = false
    0                       // had_booking = false
)
```

---

## Backward Compatibility

### Existing Code Using `verification_log`

**Files to Update**:
1. `api/visitors/stats.php` - Update to use `visitor_logs` instead of `verification_log`
2. `api/visitors/chart.php` - Update to use `visitor_logs` instead of `verification_log`
3. `admin/dashboard.php` - Update visitor statistics to use `visitor_logs`

**Migration Path**:
- Keep `verification_log` for employee verification (C# application)
- Create new `visitor_logs` table for visitor logging
- Update visitor-related endpoints to use `visitor_logs`
- `verification_log` remains unchanged for C# application compatibility

---

## External API Integration

### Service Application Data
**Requirement**: Service application data must NOT be stored locally.

**Implementation**:
- Store only `purpose` (service name) in `visitor_logs`
- Send full service application data to external API
- Log request payload to console for debugging (as required)
- Do NOT store service application payload in database

**Example Flow**:
```javascript
// 1. Log payload to console (for debugging)
console.log('External API Payload:', JSON.stringify(servicePayload, null, 2));

// 2. Send to external API
const response = await fetch(externalApiUrl, {
    method: 'POST',
    body: JSON.stringify(servicePayload)
});

// 3. Save ONLY visitor log entry (not service data)
await saveVisitorLog({
    resident_id: residentId,
    purpose: servicePayload.service_name,  // Only service name
    // ... other visitor log fields
    // DO NOT store: servicePayload.application_data, servicePayload.documents, etc.
});
```

---

## Database Migration Script

```sql
-- ============================================
-- Migration: Create visitor_logs table
-- Date: 2025-12-XX
-- Description: Add dedicated table for visitor attendance logging
-- ============================================

-- Create visitor_logs table
CREATE TABLE IF NOT EXISTS `visitor_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `is_resident` tinyint(1) NOT NULL DEFAULT 0,
  `had_booking` tinyint(1) DEFAULT 0,
  `booking_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_resident_id` (`resident_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_resident` (`is_resident`),
  KEY `idx_purpose` (`purpose`),
  CONSTRAINT `fk_visitor_logs_resident` 
    FOREIGN KEY (`resident_id`) 
    REFERENCES `residents` (`resident_id`) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add comment to table
ALTER TABLE `visitor_logs` COMMENT = 'Visitor attendance and logging records';
```

---

## Summary & Recommendations

### ✅ Recommended Actions

1. **Create `visitor_logs` table** with proposed schema
2. **Keep `verification_log` unchanged** for C# application compatibility
3. **Update visitor-related endpoints** to use `visitor_logs`
4. **Implement visitor logging logic** for all 3 scenarios
5. **Add console logging** for external API payloads (debugging only)

### ⚠️ Constraints Respected

- ✅ C# application endpoints remain unchanged
- ✅ `verification_log` table remains unchanged
- ✅ No biometric/facial data stored
- ✅ Service application data NOT stored locally
- ✅ Only visitor logging data stored

### 📋 Next Steps (After Approval)

1. Get approval for `visitor_logs` table schema
2. Create migration script
3. Update API endpoints
4. Implement visitor logging logic
5. Update statistics/charts endpoints
6. Test all 3 visitor scenarios

---

## Questions for Approval

1. **Table Name**: Is `visitor_logs` acceptable, or prefer `visitor_attendance` or `visitor_records`?

2. **Address Storage**: Should we store address as:
   - **Option A**: Single `text` field with formatted string (recommended)
   - **Option B**: Separate fields (barangay, city, province, etc.)
   - **Option C**: Reference `addresses` table (only for residents)

3. **Booking Reference**: Should we store `booking_id` even though booking data is external?
   - Current proposal: Store `booking_id` for reference only
   - Alternative: Don't store booking reference at all

4. **Migration**: Should we migrate existing `verification_log` visitor data to `visitor_logs`?
   - If yes, need to determine mapping strategy

5. **Statistics**: Should visitor statistics continue to use `verification_log` temporarily during transition, or switch immediately to `visitor_logs`?

---

**Status**: ⏳ **AWAITING APPROVAL**

Please review and approve the proposed `visitor_logs` table schema before implementation.
