# Database Files

## 🎯 For New Installations
**Use only `schema.sql`** - This file contains everything needed for a complete setup.

## 🔄 For Existing Databases (Migrations)
- `add_strands_table.sql` - Add strands table to existing databases
- Other files are deprecated (see below)

## 📁 File Status

### ✅ **ACTIVE FILES**
- **`schema.sql`** - Complete database schema (USE THIS)
- **`add_strands_table.sql`** - Migration for existing databases without strands
- **`INTEGRATION_GUIDE.md`** - Setup instructions for new PC/server

### ❌ **DEPRECATED FILES** (No longer needed)
- `auth_schema.sql` - Users table now in schema.sql
- `signup_requests_schema.sql` - Signup requests now in schema.sql  
- `teachers_signup_columns.sql` - Teacher columns now in schema.sql
- `teachers_text_fields.sql` - Teacher text fields now in schema.sql

## 🚀 Quick Setup

### New Installation:
```sql
1. Create database "student_clearance"
2. Import schema.sql
3. Done! (12 tables created)
```

### Existing Database:
```sql
1. Check if you have strands table
2. If not, run: add_strands_table.sql
3. Done!
```

## 📋 Table Count
Complete installation should have **12 tables**:
- school_year
- departments
- strands
- blocks
- subjects
- teachers
- users
- students
- signup_requests
- requirements
- student_subject
- clearance_status
- students_requirement
- students_clearance_status

## 🔐 Default Accounts
Hardcoded in `includes/auth.php`:
- Admin: `admin` / `admin123`
- Teacher: `teacher@sample.com` / `teacher`
- Student: `student@sample.com` / `student`

## ⚠️ Important Notes
- All deprecated files contain migration SQL as comments
- No need to run multiple files anymore
- Schema.sql includes all recent updates (strands table, teacher text fields, etc.)
