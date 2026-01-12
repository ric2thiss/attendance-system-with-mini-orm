# Migration Instructions - Visitor Logs Table

## Quick Migration (Recommended)

### Option 1: Via Browser (Easiest)
1. Open your browser
2. Navigate to: `http://localhost/attendance-system/database/migrations/run_migration.php`
3. The migration will run automatically
4. Check the output for success/error messages

### Option 2: Via phpMyAdmin
1. Open phpMyAdmin
2. Select `attendance-system` database
3. Go to **Import** tab
4. Click **Choose File**
5. Select: `database/migrations/create_visitor_logs_table.sql`
6. Click **Go**
7. Verify the `visitor_logs` table was created

### Option 3: Via MySQL Command Line
```bash
mysql -u root -p attendance-system < database/migrations/create_visitor_logs_table.sql
```

## Verify Migration

After running the migration, verify the table was created:

```sql
-- Check if table exists
SHOW TABLES LIKE 'visitor_logs';

-- Check table structure
DESCRIBE visitor_logs;

-- Check indexes
SHOW INDEXES FROM visitor_logs;
```

Expected output:
- Table `visitor_logs` should exist
- Should have columns: id, resident_id, first_name, middle_name, last_name, birthdate, address, purpose, is_resident, had_booking, booking_id, created_at, updated_at
- Should have indexes: idx_resident_id, idx_created_at, idx_is_resident, idx_purpose
- Should have foreign key: fk_visitor_logs_resident

## Troubleshooting

### Table Already Exists
If you see "Table already exists" error:
- This is normal if migration was run before
- The table structure is preserved
- You can continue using the system

### Foreign Key Error
If you see foreign key constraint error:
- Make sure `residents` table exists
- Check that `residents.resident_id` column exists
- Verify database engine is InnoDB

### Permission Error
If you see permission errors:
- Make sure database user has CREATE TABLE permission
- Check database user has ALTER TABLE permission
- Verify database user has INDEX creation permission

## Rollback (If Needed)

If you need to remove the table:

```sql
-- Remove foreign key constraint first
ALTER TABLE visitor_logs DROP FOREIGN KEY fk_visitor_logs_resident;

-- Drop the table
DROP TABLE IF EXISTS visitor_logs;
```

**Warning**: This will delete all visitor log data!

## Post-Migration Checklist

- [ ] Table `visitor_logs` created successfully
- [ ] All columns present
- [ ] Indexes created
- [ ] Foreign key constraint created
- [ ] Test visitor logging (Scenario 1, 2, 3)
- [ ] Verify data is being saved correctly

## Next Steps

After successful migration:
1. Test Scenario 1: Resident with booking
2. Test Scenario 2: Resident without booking  
3. Test Scenario 3: Non-resident visitor
4. Check visitor logs in database
5. Verify statistics endpoints work correctly
