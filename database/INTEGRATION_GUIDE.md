# Gradline SHS Clearance - Integration Guide

## Quick Setup for New PC/Server

### 1. Database Setup
1. Create empty database named `student_clearance` in phpMyAdmin
2. Import `schema.sql` - this creates all tables with latest updates
3. **No need to run other SQL files** - everything is included in main schema

### 2. Configuration
- Update `config/database.php` with your database credentials
- Ensure web server points to `public/` directory as document root

### 3. Default Login Accounts
The system includes hardcoded accounts that work immediately:

| Role | Username | Password | Access |
|------|----------|----------|--------|
| Admin | `admin` | `admin123` | Full system access |
| Teacher | `teacher@sample.com` | `teacher` | Teacher dashboard |
| Student | `student@sample.com` | `student` | Student dashboard |

### 4. Initial Data Setup
1. **Login as Admin** using hardcoded credentials
2. Go to **Master Data → Departments** - Add your departments
3. For each department, add **Strands** 
4. For each strand, add **Subjects**
5. Add **Teachers** and assign them to departments
6. Add **Students** and assign them to blocks/strands
7. Set up **Requirements** for each department

## Recent System Updates

### ✅ Strands Management System
- **New `strands` table** - strands can exist without subjects
- **Hierarchical structure**: Departments → Strands → Subjects
- **Proper foreign key relationships** with cascade delete

### ✅ Enhanced Teachers Table
- Added `department` and `strand` text fields for flexibility
- Supports both structured and text-based department/strand assignments

### ✅ Updated Navigation
- Removed "Subjects" from admin dropdown
- Subjects now accessed via: Departments → [Department] → [Strand]

### ✅ Fixed Issues
- Strand creation now works properly across all departments
- Edit subject page no longer shows undefined variable warnings
- Teachers index page handles missing database columns gracefully

## File Structure Overview

```
student_clearance/
├── database/
│   ├── schema.sql              # Complete database schema (RUN THIS)
│   ├── add_strands_table.sql   # Migration for existing databases
│   └── INTEGRATION_GUIDE.md    # This file
├── admin/                      # Admin panel
│   ├── departments/           # Department & strand management
│   ├── subjects/              # Subject management (accessed via strands)
│   ├── teachers/              # Teacher management
│   └── students/              # Student management
├── teacher/                   # Teacher dashboard
├── public/                    # Public pages (login, signup, dashboard)
└── includes/
    ├── auth.php               # Hardcoded login accounts
    └── header.php             # Navigation (Subjects dropdown removed)
```

## Common Integration Issues & Solutions

### Issue: "Column not found: t.department"
**Solution**: The schema already includes the fix. Teachers page will work with or without text columns.

### Issue: "Strands don't appear after creation"
**Solution**: Strands table is now properly created. Strands appear immediately in department view.

### Issue: "Subjects dropdown missing"
**Solution**: This is intentional. Access subjects via Departments → [Department] → [Strand].

### Issue: "Login not working"
**Solution**: Use hardcoded accounts listed above. They work regardless of database state.

## Database Schema Highlights

### Core Tables
- `departments` - Academic departments
- `strands` - Academic strands (linked to departments)
- `subjects` - Subjects (linked to strands and departments)
- `teachers` - Teacher accounts with department assignments
- `students` - Student accounts with block/strand assignments
- `users` - Login accounts (student/teacher/admin)

### Relationships
- Department → Strands (1:N)
- Strand → Subjects (1:N)
- Department → Teachers (1:N)
- Department → Requirements (1:N)

## Security Notes
- Hardcoded accounts are for demo/testing purposes
- Change admin password in production via `includes/auth.php`
- Database credentials in `config/database.php` should be secured
- System uses prepared statements to prevent SQL injection

## Support
For issues during integration:
1. Check that all tables were created (should be 12 tables total)
2. Verify database credentials in config
3. Test with hardcoded admin account first
4. Check PHP error logs for detailed messages
