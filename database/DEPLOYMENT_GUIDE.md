# Senior High School Clearance System - Deployment Guide

## Overview
This guide helps you safely deploy the complete clearance system with compliance tracking to other devices or environments.

## Files Needed
1. `complete_schema_with_compliance_system.sql` - Complete database schema
2. The entire `student_clearance` folder (application files)

## Deployment Options

### Option 1: Fresh Installation (Recommended)
**Best for:** New devices, clean setup, no existing data

**Steps:**
1. **Setup Web Server**
   - Install XAMPP/WAMP/MAMP on target device
   - Ensure Apache and MySQL are running
   - Create database: `student_clearance`

2. **Import Database Schema**
   ```sql
   -- In phpMyAdmin or MySQL command line:
   USE student_clearance;
   SOURCE complete_schema_with_compliance_system.sql;
   ```

3. **Configure Application**
   - Copy entire `student_clearance` folder to web root
   - Update `config/database.php` with new database credentials
   - Set proper file permissions (755 for folders, 644 for files)

4. **Create Admin User**
   - Default admin: username `admin`, password `admin123`
   - Change password immediately after first login

### Option 2: Data Migration
**Best for:** Moving existing data to new device

**Steps:**
1. **Export Existing Data**
   ```sql
   -- On source device:
   mysqldump -u root -p student_clearance > existing_data.sql
   ```

2. **Import Schema Only**
   ```sql
   -- On target device:
   USE student_clearance;
   SOURCE complete_schema_with_compliance_system.sql;
   ```

3. **Migrate Data**
   ```sql
   -- Import existing data (will update schema automatically):
   SOURCE existing_data.sql;
   ```

## Configuration Checklist

### Database Configuration (`config/database.php`)
```php
$host = 'localhost';
$dbname = 'student_clearance';
$username = 'root';
$password = ''; // Your MySQL password
```

### Web Server Requirements
- ✅ PHP 7.4 or higher
- ✅ MySQL 5.7 or higher / MariaDB 10.2+
- ✅ Apache with mod_rewrite enabled
- ✅ PDO PHP extension enabled

### File Permissions
```bash
# Set appropriate permissions
chmod 755 student_clearance/
chmod 755 student_clearance/uploads/
chmod 644 student_clearance/*.php
chmod 644 student_clearance/config/*.php
```

## Post-Deployment Setup

### 1. Test Database Connection
- Visit `http://localhost/student_clearance/public/`
- Should see login page (no database errors)

### 2. Create Initial Data
- Login as admin (admin/admin123)
- Add departments, strands, school years
- Add teachers and students
- Create subjects and requirements

### 3. Test Compliance System
- Create test student clearance request
- Teacher returns for compliance
- Student views compliance details
- Student resubmits request
- Verify all functionality works

## Security Considerations

### Database Security
```sql
-- Create dedicated database user (recommended)
CREATE USER 'clearance_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON student_clearance.* TO 'clearance_user'@'localhost';
FLUSH PRIVILEGES;
```

### Application Security
- Change default admin password immediately
- Update database credentials in `config/database.php`
- Ensure `.htaccess` files are present in sensitive directories
- Set proper file permissions (no world-writable files)

### Production Environment
- Disable PHP error display: `display_errors = Off`
- Enable error logging: `log_errors = On`
- Use HTTPS in production
- Regular database backups

## Troubleshooting

### Common Issues

**1. Database Connection Error**
```
Error: could not find driver
```
**Solution:** Enable PDO MySQL extension in php.ini

**2. Column Not Found Error**
```
Unknown column 'date_returned'
```
**Solution:** Run the complete schema import, it handles column creation automatically

**3. Permission Denied**
```
Access denied for user
```
**Solution:** Check database credentials and user permissions

**4. Blank Pages**
```
White screen of death
```
**Solution:** Check PHP error logs, enable error display for debugging

### Data Validation
After deployment, verify:
- ✅ All tables created correctly
- ✅ Default data inserted (departments, strands, requirements)
- ✅ Admin user can login
- ✅ Student/Teacher registration works
- ✅ Clearance request flow works
- ✅ Compliance system functions

## Backup Strategy

### Regular Backups
```bash
# Full database backup
mysqldump -u root -p student_clearance > backup_$(date +%Y%m%d).sql

# Application files backup
tar -czf student_clearance_$(date +%Y%m%d).tar.gz student_clearance/
```

### Automated Backup Script
```bash
#!/bin/bash
# backup_clearance.sh
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p student_clearance > /backups/db_$DATE.sql
tar -czf /backups/app_$DATE.tar.gz student_clearance/
```

## Migration Between Environments

### Development → Staging → Production
1. **Development:** Test all features with sample data
2. **Staging:** Import production data, test with real scenarios
3. **Production:** Final deployment with live data

### Data Sync
```sql
-- For syncing specific tables
INSERT INTO target_db.clearance_status 
SELECT * FROM source_db.clearance_status 
WHERE date_created > '2025-01-01';
```

## Performance Optimization

### Database Indexes
The schema includes optimized indexes for:
- Student lookups
- Teacher workloads
- Compliance tracking
- Date-based queries

### Caching
- Enable PHP OPcache
- Consider Redis for session storage
- Use browser caching for static assets

## Support

For issues during deployment:
1. Check error logs: `Apache/logs/error.log`, `MySQL/logs/error.log`
2. Verify database schema: `DESCRIBE clearance_status;`
3. Test database connection: Create simple PHP test script
4. Review file permissions and ownership

## Version Compatibility

This schema is compatible with:
- MySQL 5.7+
- MariaDB 10.2+
- PHP 7.4+
- Apache 2.4+

For older versions, manual adjustments may be needed.
