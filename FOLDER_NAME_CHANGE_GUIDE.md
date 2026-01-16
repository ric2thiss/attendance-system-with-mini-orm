# Folder Name Change Guide

## ✅ System is Now Folder-Name Agnostic!

The attendance system has been updated to **automatically detect** its base URL regardless of the folder name or domain. You can now rename the folder from `attendance-system` to `attendance` (or any other name) without breaking the system.

## What Was Changed

### 1. **PHP Configuration** (`config/app.config.php`)
- ✅ Base URL is now **automatically detected** from the current directory structure
- ✅ Works with any folder name (e.g., `attendance`, `attendance-system`, `my-app`)
- ✅ Works with any domain (localhost, production domain, etc.)

### 2. **PHP Files Updated**
All PHP files now use the `BASE_URL` constant instead of hardcoded paths:
- ✅ `auth/logout.php`
- ✅ `auth/login.php`
- ✅ `auth/helpers.php`
- ✅ `app/controller/AuthController.php`
- ✅ `shared/components/Sidebar.php`
- ✅ `shared/components/PayrollSidebar.php`
- ✅ `api/visitors/residents.php`

### 3. **JavaScript Files Updated**
JavaScript files now use a dynamic base URL utility:
- ✅ Created `admin/js/shared/baseUrl.js` - automatically detects base URL
- ✅ Updated `admin/js/payroll/payrunProcessor.js`
- ✅ Updated `admin/js/payroll/tableRenderer.js`
- ✅ Updated `admin/js/payroll/cardUpdater.js`
- ✅ Updated `admin/js/payroll/passwordConfirm.js`

### 4. **WebSocket Configuration** (`websocket/config.js`)
- ✅ Base URL is now **automatically detected** from the project folder name
- ✅ Can be overridden with `BASE_URL` environment variable

### 5. **C# Application Files**
C# files now use a configurable `BASE_URL` constant:
- ✅ `Enrollment.cs` - Change `BASE_URL` constant at the top of the file
- ✅ `Identification.cs` - Change `BASE_URL` constant at the top of the file
- ✅ `Verification.cs` - Change `BASE_URL` constant at the top of the file

## How to Change the Folder Name

### For PHP/Web Application:
1. **Rename the folder** (e.g., from `attendance-system` to `attendance`)
2. **That's it!** The system will automatically detect the new folder name

### For C# Application:
1. Open each C# file (`Enrollment.cs`, `Identification.cs`, `Verification.cs`)
2. Find the `BASE_URL` constant at the top of each file
3. Update it to match your new folder name:
   ```csharp
   private const string BASE_URL = "http://localhost/attendance"; // Changed from attendance-system
   ```
4. Recompile the C# application

### For WebSocket Server:
1. The WebSocket server will automatically detect the folder name
2. **Optional**: Set `BASE_URL` environment variable if needed:
   ```bash
   export BASE_URL="http://localhost/attendance"
   ```

## Testing After Folder Rename

After renaming the folder, test these areas:

1. ✅ **Login/Logout** - Should redirect correctly
2. ✅ **Navigation** - All links should work
3. ✅ **API Calls** - JavaScript API calls should work
4. ✅ **Payroll Module** - All payroll features should work
5. ✅ **WebSocket** - Real-time updates should work
6. ✅ **C# Application** - Update BASE_URL constant and test

## Environment Variables (Optional)

You can override the auto-detection using environment variables:

### PHP (via `.htaccess` or server config):
```apache
SetEnv BASE_URL "http://localhost/attendance"
```

### WebSocket (Node.js):
```bash
export BASE_URL="http://localhost/attendance"
```

### C# (App.config):
```xml
<appSettings>
  <add key="BaseUrl" value="http://localhost/attendance" />
</appSettings>
```

## Notes

- The system uses **relative paths** where possible
- **Absolute URLs** are generated dynamically from the current request
- The **database name** is separate from the folder name (no changes needed)
- **File system paths** use `__DIR__` which is always correct

## Troubleshooting

If something doesn't work after renaming:

1. **Clear browser cache** - Old JavaScript might be cached
2. **Check PHP error logs** - Look for BASE_URL related errors
3. **Verify WebSocket config** - Check `websocket/config.js` is detecting correctly
4. **Update C# BASE_URL** - Make sure all C# files have the correct BASE_URL
5. **Check .htaccess** - Ensure rewrite rules still work with new folder name

## Summary

✅ **PHP**: Fully automatic - no changes needed  
✅ **JavaScript**: Fully automatic - no changes needed  
✅ **WebSocket**: Fully automatic - no changes needed  
⚠️ **C#**: Manual update required - change BASE_URL constant in 3 files

The system is now **production-ready** and can be deployed to any folder name or domain without code changes!
