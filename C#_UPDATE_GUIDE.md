# C# Application Update Guide - Flexible Enrollment

## Overview

The PHP backend (`enroll.php`) has been updated to accept both `employee_id` and `resident_id`. The C# application needs to be updated to:

1. Accept `resident_id` parameter from URL (in addition to `employee_id`)
2. Send the appropriate ID to the enrollment endpoint
3. Handle success redirects for both employee and resident

## Changes Required

### 1. Update Program.cs

**File:** `Program.cs`

**Current Code (lines 19-41):**
```csharp
if (args.Length > 0 && args[0].StartsWith("biometrics://"))
{
    string employeeId = null;
    string action = null;

    try
    {
        var uri = new Uri(args[0]);
        action = uri.Host; // "enroll" or "identify"

        if (!string.IsNullOrEmpty(uri.Query))
        {
            var queryParams = uri.Query.TrimStart('?').Split('&');
            foreach (var param in queryParams)
            {
                var parts = param.Split('=');
                if (parts.Length == 2 && parts[0] == "employee_id")
                {
                    employeeId = Uri.UnescapeDataString(parts[1]);
                    break;
                }
            }
        }
    }
    // ... rest of code
```

**Updated Code:**
```csharp
if (args.Length > 0 && args[0].StartsWith("biometrics://"))
{
    string employeeId = null;
    string residentId = null;
    string action = null;

    try
    {
        var uri = new Uri(args[0]);
        action = uri.Host; // "enroll" or "identify"

        if (!string.IsNullOrEmpty(uri.Query))
        {
            var queryParams = uri.Query.TrimStart('?').Split('&');
            foreach (var param in queryParams)
            {
                var parts = param.Split('=');
                if (parts.Length == 2)
                {
                    if (parts[0] == "employee_id")
                    {
                        employeeId = Uri.UnescapeDataString(parts[1]);
                    }
                    else if (parts[0] == "resident_id")
                    {
                        residentId = Uri.UnescapeDataString(parts[1]);
                    }
                }
            }
        }
    }
    // ... rest of code
```

**Also update the form launch (around line 51-53):**
```csharp
if (action == "enroll")
{
    formToRun = new Enrollment(employeeId, residentId) { _sender = mainForm };
}
```

### 2. Update Enrollment.cs Constructor

**File:** `Enrollment.cs`

**Current Constructor (lines 28-34):**
```csharp
public Enrollment(string employeeId) : this()
{
    if (!string.IsNullOrWhiteSpace(employeeId))
    {
        _employeeId = employeeId;
    }
}
```

**Updated Constructor:**
```csharp
private string _residentId;

public Enrollment(string employeeId) : this(employeeId, null)
{
}

public Enrollment(string employeeId, string residentId) : this()
{
    if (!string.IsNullOrWhiteSpace(employeeId))
    {
        _employeeId = employeeId;
    }
    if (!string.IsNullOrWhiteSpace(residentId))
    {
        _residentId = residentId;
    }
}
```

### 3. Update SendEnrollToApiAsync Method

**File:** `Enrollment.cs`

**Current Method (lines 146-184):**
```csharp
private async Task SendEnrollToApiAsync(string employeeId, string templateBase64)
{
    var data = new
    {
        employee_id = employeeId,
        template = templateBase64
    };

    var json = JsonConvert.SerializeObject(data);
    var content = new StringContent(json, Encoding.UTF8, "application/json");

    using (var client = new HttpClient())
    {
        try
        {
            var response = await client.PostAsync("http://localhost/attendance-system/enroll.php", content);

            if (response.IsSuccessStatusCode)
            {
                MessageBox.Show("✅ Fingerprint enrolled successfully.", "Success", MessageBoxButtons.OK, MessageBoxIcon.Information);
                System.Diagnostics.Process.Start("http://localhost/attendance-system/biometric-success.php?employee_id=" + _employeeId);
            }
            // ... error handling
        }
        // ... catch block
    }
}
```

**Updated Method:**
```csharp
private async Task SendEnrollToApiAsync(string templateBase64)
{
    // Determine which ID to use (employee_id takes precedence for backward compatibility)
    bool isEmployee = !string.IsNullOrWhiteSpace(_employeeId);
    bool isResident = !string.IsNullOrWhiteSpace(_residentId);
    
    if (!isEmployee && !isResident)
    {
        MessageBox.Show("⚠️ Enrollment cancelled: No ID provided from browser.",
            "Cancelled", MessageBoxButtons.OK, MessageBoxIcon.Warning);
        return;
    }

    object data;
    string successUrl;
    
    if (isEmployee)
    {
        data = new
        {
            employee_id = _employeeId,
            template = templateBase64
        };
        successUrl = "http://localhost/attendance-system/biometric-success.php?employee_id=" + _employeeId;
    }
    else
    {
        data = new
        {
            resident_id = _residentId,
            template = templateBase64
        };
        successUrl = "http://localhost/attendance-system/biometric-success.php?resident_id=" + _residentId;
    }

    var json = JsonConvert.SerializeObject(data);
    var content = new StringContent(json, Encoding.UTF8, "application/json");

    using (var client = new HttpClient())
    {
        try
        {
            var response = await client.PostAsync("http://localhost/attendance-system/enroll.php", content);

            if (response.IsSuccessStatusCode)
            {
                MessageBox.Show("✅ Fingerprint enrolled successfully.", "Success", MessageBoxButtons.OK, MessageBoxIcon.Information);
                System.Diagnostics.Process.Start(successUrl);
            }
            else
            {
                string responseBody = await response.Content.ReadAsStringAsync();
                MessageBox.Show(" Fingerprint already enrolled.", "Failed", MessageBoxButtons.OK, MessageBoxIcon.Information);
                SendMessage(Action.SendMessage, $"❌ Enrollment failed. Status: {response.StatusCode}\n{responseBody}");
            }
        }
        catch (Exception ex)
        {
            MessageBox.Show($"❌ Error sending to server: {ex.Message}", "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
        }
    }
}
```

### 4. Update OnCaptured Method Call

**File:** `Enrollment.cs`

**Current Call (around line 234):**
```csharp
await SendEnrollToApiAsync(_employeeId, fmdBase64);
```

**Updated Call:**
```csharp
await SendEnrollToApiAsync(fmdBase64);
```

### 5. Update Validation Check

**File:** `Enrollment.cs`

**Current Check (around lines 227-232):**
```csharp
if (string.IsNullOrWhiteSpace(_employeeId))
{
    MessageBox.Show("⚠️ Enrollment cancelled: Employee ID not provided from browser.",
        "Cancelled", MessageBoxButtons.OK, MessageBoxIcon.Warning);
    return;
}
```

**Updated Check:**
```csharp
if (string.IsNullOrWhiteSpace(_employeeId) && string.IsNullOrWhiteSpace(_residentId))
{
    MessageBox.Show("⚠️ Enrollment cancelled: No ID provided from browser.",
        "Cancelled", MessageBoxButtons.OK, MessageBoxIcon.Warning);
    return;
}
```

## Summary of Changes

1. **Program.cs**: Extract both `employee_id` and `resident_id` from URL parameters
2. **Enrollment.cs**: 
   - Add `_residentId` field
   - Update constructor to accept both IDs
   - Update `SendEnrollToApiAsync` to use appropriate ID and success URL
   - Update validation to check for either ID

## Testing

After making these changes:

1. **Test Employee Enrollment:**
   - Use: `biometrics://enroll?employee_id=20201188`
   - Should send to `enroll.php` with `employee_id`
   - Should redirect to `biometric-success.php?employee_id=20201188`

2. **Test Resident Enrollment:**
   - Use: `biometrics://enroll?resident_id=9`
   - Should send to `enroll.php` with `resident_id`
   - Should redirect to `biometric-success.php?resident_id=9`

## Backward Compatibility

✅ The changes maintain backward compatibility:
- Existing employee enrollment URLs still work
- The `Enrollment(string employeeId)` constructor is preserved
- Employee enrollment flow remains unchanged
