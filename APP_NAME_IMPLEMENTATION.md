# Application Name from Database - Implementation Summary

## Overview
The sidebar now dynamically displays the application name from the database settings table, allowing administrators to customize the application name through the Settings page.

## Changes Made

### 1. Sidebar Component (PHP)
**File:** `attendance-system/shared/components/Sidebar.php`

Added an `id="app-name"` attribute to the application name span element to allow JavaScript to update it dynamically:

```php
<span id="app-name" class="text-xl font-bold tracking-tight leading-tight">Attendance System</span>
```

The default "Attendance System" text serves as a fallback while the page loads.

### 2. Public Settings API Endpoint
**File:** `attendance-system/api/settings/public.php` (NEW)

Created a new public API endpoint that:
- Is accessible by all authenticated users (not just administrators)
- Returns only the `app_name` setting for security
- Provides fallback to "Attendance System" if the setting is not found
- Handles errors gracefully

**Endpoint:** `GET /api/settings/public.php`

**Response format:**
```json
{
  "success": true,
  "data": {
    "app_name": {
      "value": "Attendance System",
      "type": "string"
    }
  }
}
```

### 3. Sidebar JavaScript Module
**File:** `attendance-system/admin/js/shared/sidebar.js`

Enhanced the sidebar initialization to:
- Fetch the app name from the public settings API
- Update the sidebar app name element dynamically
- Provide fallback to "Attendance System" if fetch fails

**New functions:**
- `fetchAppName()`: Async function that fetches app name from API
- `updateAppName()`: Updates the DOM element with the fetched app name

## How It Works

1. **Page Load**: When any admin page loads, the sidebar is rendered with the default "Attendance System" text
2. **Sidebar Initialization**: The `initSidebar()` function is called (already present in all admin pages)
3. **API Call**: The sidebar module automatically fetches the app name from `/api/settings/public.php`
4. **DOM Update**: Once fetched, the app name is updated in the sidebar
5. **Fallback**: If the API fails or returns an error, the default "Attendance System" remains displayed

## Affected Pages

All admin pages that use the sidebar will automatically display the app name from the database:
- Dashboard
- Employees
- Residents
- Biometric Registration
- Attendance
- DTR
- Visitors
- Payroll
- Attendance Reports
- Visitor Reports
- Master Lists
- Accounts
- Settings

## Database Structure

The app name is stored in the `settings` table:

```sql
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('string','boolean','integer','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
);

INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `description`) VALUES
('app_name', 'Attendance System', 'string', 'Application name');
```

## Configuration

Administrators can change the application name by:
1. Going to the Settings page (`/admin/settings.php`)
2. Updating the "Application Name" field
3. Saving the settings

The new name will be displayed in all sidebars across the system immediately upon the next page load.

## Security

- The public settings endpoint requires authentication (any authenticated user can access it)
- Only the `app_name` setting is exposed through the public endpoint
- Sensitive settings remain protected by the admin-only settings endpoint
- All errors are logged and fallback values are returned

## Testing

To test the implementation:
1. Ensure the `settings` table exists in the database
2. Navigate to any admin page (e.g., `/admin/visitor-reports.php`)
3. Observe the sidebar displaying the app name from the database
4. Open browser DevTools and check the Network tab for the API call to `/api/settings/public.php`
5. Change the app name in Settings page and verify it updates across all pages

## Compatibility

- Works with all existing admin pages
- No breaking changes to existing functionality
- Backward compatible with default "Attendance System" name
- Graceful degradation if API fails
