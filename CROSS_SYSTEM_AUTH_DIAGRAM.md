# Cross-System Authentication Flow Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         USER AUTHENTICATION                          │
└─────────────────────────────────────────────────────────────────────┘
                                 │
                    ┌────────────┴────────────┐
                    │                         │
                    ▼                         ▼
        ┌───────────────────┐     ┌──────────────────┐
        │  login.php        │     │ attendance-system│
        │  (profiling)      │     │ /auth/login.php  │
        └───────────────────┘     └──────────────────┘
                    │                         │
                    │                         │
                    ▼                         ▼
        ┌───────────────────┐     ┌──────────────────────────────┐
        │ Profiling-System  │     │   AuthController::login()    │
        │ Authentication    │     │                              │
        │                   │     │  STEP 1: Check attendance-   │
        │ 1. admin          │     │          system.admins       │
        │ 2. barangay_      │     │          ↓ (not found)       │
        │    official       │     │  STEP 2: Check profiling-    │
        │ 3. residents      │     │          system.admin        │
        │                   │     │          ↓ (not found)       │
        │ Sets:             │     │  STEP 3: Check profiling-    │
        │ • user_id         │     │          system.barangay_    │
        │ • username        │     │          official            │
        │ • role            │     │          ↓ (not found)       │
        │ • name            │     │  STEP 4: Check profiling-    │
        │ • admin_id        │     │          system.residents    │
        │ • admin_username  │     │                              │
        │ • admin_email     │     │  Sets ALL session variables  │
        │ • admin_full_name │     │  (both systems)              │
        │ • admin_role      │     │                              │
        │ • is_authenticated│     │                              │
        │ • login_time      │     │                              │
        └───────────────────┘     └──────────────────────────────┘
                    │                         │
                    │                         │
                    └────────────┬────────────┘
                                 │
                                 ▼
                    ┌────────────────────────┐
                    │   SESSION VARIABLES    │
                    │   (Compatible Format)  │
                    │                        │
                    │ Profiling-System Keys: │
                    │ • user_id              │
                    │ • username             │
                    │ • role                 │
                    │ • name                 │
                    │                        │
                    │ Attendance-System Keys:│
                    │ • admin_id             │
                    │ • admin_username       │
                    │ • admin_email          │
                    │ • admin_full_name      │
                    │ • admin_role           │
                    │ • is_authenticated     │
                    │ • login_time           │
                    │ • auth_source          │
                    └────────────────────────┘
                                 │
                    ┌────────────┴────────────┐
                    │                         │
                    ▼                         ▼
        ┌───────────────────┐     ┌──────────────────┐
        │  dashboard.php    │     │ attendance-system│
        │  (profiling)      │     │ /admin/dashboard │
        │                   │     │                  │
        │  Checks:          │     │  Checks:         │
        │  • user_id        │     │  • is_authenticated│
        │  • role           │     │  • admin_id      │
        │  • name           │     │  • admin_role    │
        │                   │     │                  │
        │  ✓ ACCESS GRANTED │     │  ✓ ACCESS GRANTED│
        └───────────────────┘     └──────────────────┘
```

## Database Structure

```
┌─────────────────────────────────────────────────────────────────────┐
│                         DATABASE LAYER                               │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────────────┐         ┌──────────────────────────┐
│  attendance-system DB    │         │  profiling-system DB     │
│                          │         │                          │
│  ┌────────────────────┐  │         │  ┌────────────────────┐  │
│  │ admins             │  │         │  │ admin              │  │
│  ├────────────────────┤  │         │  ├────────────────────┤  │
│  │ id                 │  │         │  │ id                 │  │
│  │ username           │  │         │  │ username           │  │
│  │ email              │  │         │  │ password           │  │
│  │ password           │  │         │  │ name               │  │
│  │ full_name          │  │         │  │ email              │  │
│  │ role               │  │         │  └────────────────────┘  │
│  │ is_active          │  │         │                          │
│  └────────────────────┘  │         │  ┌────────────────────┐  │
│                          │         │  │ barangay_official  │  │
│  Connection:             │         │  ├────────────────────┤  │
│  Database::connect()     │         │  │ id                 │  │
│                          │         │  │ username           │  │
│                          │         │  │ password           │  │
│                          │         │  │ first_name         │  │
│                          │         │  │ surname            │  │
│                          │         │  │ position           │  │
│                          │         │  │ email              │  │
│                          │         │  └────────────────────┘  │
│                          │         │                          │
│                          │         │  ┌────────────────────┐  │
│                          │         │  │ residents          │  │
│                          │         │  ├────────────────────┤  │
│                          │         │  │ id                 │  │
│                          │         │  │ username           │  │
│                          │         │  │ password           │  │
│                          │         │  │ first_name         │  │
│                          │         │  │ surname            │  │
│                          │         │  │ email              │  │
│                          │         │  └────────────────────┘  │
│                          │         │                          │
│                          │         │  Connection:             │
│                          │         │  AuthController::        │
│                          │         │  getProfilingDbConnection│
└──────────────────────────┘         └──────────────────────────┘
```

## Authentication Decision Tree

```
                        User Login Attempt
                                │
                                ▼
                    ┌───────────────────────┐
                    │ Which Login Page?     │
                    └───────────────────────┘
                                │
                ┌───────────────┴───────────────┐
                │                               │
                ▼                               ▼
    ┌─────────────────────┐         ┌─────────────────────┐
    │ login.php           │         │ attendance-system   │
    │ (profiling)         │         │ /auth/login.php     │
    └─────────────────────┘         └─────────────────────┘
                │                               │
                ▼                               ▼
    ┌─────────────────────┐         ┌─────────────────────┐
    │ Check profiling-    │         │ Check attendance-   │
    │ system.admin        │         │ system.admins       │
    └─────────────────────┘         └─────────────────────┘
                │                               │
        ┌───────┴───────┐               ┌───────┴───────┐
        │               │               │               │
    FOUND           NOT FOUND       FOUND           NOT FOUND
        │               │               │               │
        ▼               ▼               ▼               ▼
    SUCCESS    ┌─────────────┐      SUCCESS    ┌─────────────┐
               │ Check       │                 │ Check       │
               │ barangay_   │                 │ profiling-  │
               │ official    │                 │ system.admin│
               └─────────────┘                 └─────────────┘
                      │                               │
              ┌───────┴───────┐               ┌───────┴───────┐
              │               │               │               │
          FOUND           NOT FOUND       FOUND           NOT FOUND
              │               │               │               │
              ▼               ▼               ▼               ▼
          SUCCESS    ┌─────────────┐      SUCCESS    ┌─────────────┐
                     │ Check       │                 │ Check       │
                     │ residents   │                 │ barangay_   │
                     └─────────────┘                 │ official    │
                            │                        └─────────────┘
                    ┌───────┴───────┐                       │
                    │               │               ┌───────┴───────┐
                FOUND           NOT FOUND           │               │
                    │               │           FOUND           NOT FOUND
                    ▼               ▼               │               │
                SUCCESS         FAIL                ▼               ▼
                                                SUCCESS    ┌─────────────┐
                                                           │ Check       │
                                                           │ residents   │
                                                           └─────────────┘
                                                                  │
                                                          ┌───────┴───────┐
                                                          │               │
                                                      FOUND           NOT FOUND
                                                          │               │
                                                          ▼               ▼
                                                      SUCCESS         FAIL
```

## Session Variable Compatibility Matrix

```
┌────────────────────┬─────────────────┬─────────────────┬──────────────┐
│ Purpose            │ Profiling Key   │ Attendance Key  │ Value        │
├────────────────────┼─────────────────┼─────────────────┼──────────────┤
│ User ID            │ user_id         │ admin_id        │ Same         │
│ Username           │ username        │ admin_username  │ Same         │
│ Email              │ (none)          │ admin_email     │ From DB      │
│ Full Name          │ name            │ admin_full_name │ Same         │
│ Role/Position      │ role            │ admin_role      │ Same         │
│ Auth Flag          │ (none)          │ is_authenticated│ true         │
│ Login Timestamp    │ (none)          │ login_time      │ time()       │
│ Auth Source Track  │ (none)          │ auth_source     │ Table name   │
└────────────────────┴─────────────────┴─────────────────┴──────────────┘

✓ Both systems set ALL variables
✓ No conflicts between keys
✓ Values are synchronized
```

## Security Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                         SECURITY LAYERS                              │
└─────────────────────────────────────────────────────────────────────┘

1. Input Validation
   ├─ Username: trim(), not empty
   └─ Password: not empty

2. Database Query
   ├─ Prepared statements (SQL injection protection)
   └─ Parameterized queries

3. Password Verification
   ├─ password_verify() for hashed passwords
   ├─ Plain text comparison for legacy (admin/residents only)
   └─ No password in error messages

4. Account Status Check (attendance-system admins only)
   ├─ is_active = 1 (active)
   └─ is_active = 0 (locked) → Deny with message

5. Session Security
   ├─ session_start() if not started
   ├─ Session variables set only on success
   └─ No session data on failure

6. Error Handling
   ├─ Generic error messages (no info leakage)
   ├─ Detailed logging via error_log()
   └─ Database errors caught and logged
```

---

**Legend:**
- `→` : Data flow
- `▼` : Process step
- `✓` : Success/Valid
- `┌─┐` : Container/Box
- `├─┤` : Table structure
