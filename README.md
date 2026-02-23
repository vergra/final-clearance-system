# Gradline: Senior High School Clearance System

A simple CRUD web application that digitizes the student clearance process. Built with **PHP**, **HTML**, **CSS**, **Bootstrap**, and **MySQL**, designed to run on **XAMPP**.

## Features

- **User roles**: **Student**, **Teacher**, and **Admin** accounts with login. Students see only their clearance; teachers can manage clearance; admins manage all data and user accounts.
- **Master data**: School Years, Departments, Blocks, Teachers, Subjects, Requirements (admin only)
- **Students**: LRN, name, strand, block (admin only)
- **Enrollment**: Assign students to subjects per school year (admin only)
- **Clearance**: Create and track clearance status per student, requirement, teacher, and school year (Pending / Approved / Declined). Teachers and admins manage clearance; students view **My Clearance** only.

The database structure follows the provided Entity Relationship Diagram (ERD), plus a `users` table for authentication.

## Requirements

- XAMPP (Apache + MySQL + PHP)
- Modern browser

## Setup (XAMPP)

1. **Start XAMPP**  
   - Open XAMPP Control Panel and start **Apache** and **MySQL**.

2. **Place the project**  
   - Copy the `student_clearance` folder to the XAMPP document root:
     - **Windows**: `C:\xampp\htdocs\student_clearance`
     - **Mac/Linux**: `/Applications/XAMPP/htdocs/student_clearance` or `/opt/lampp/htdocs/student_clearance`

3. **Create the database**  
   - Open **phpMyAdmin**: `http://localhost/phpmyadmin`
   - Create a new database **student_clearance** (if needed), then Import → choose `database/schema.sql` (this creates all tables, including **users**, and sample data).
   - If you already have the database and only need login support, run `database/auth_schema.sql` to add the `users` table and a default admin account.

4. **Database configuration**  
   - The app is set to use database **student_clearance**. Edit `config/database.php` if your MySQL user/password differ (default XAMPP: user `root`, password empty).

5. **Open the application**  
   - In the browser go to: **http://localhost/student_clearance/**  
   - You will be redirected to **Login**. Default admin: username **admin**, password **password** (change in production).
   - After login, the dashboard and menu depend on your role: **Admin** sees full master data and User accounts; **Teacher** sees Clearance; **Student** sees **My Clearance** only.
   - To create student or teacher logins: log in as admin → Master Data → **User accounts** → Add User (choose role and link to a student LRN or teacher).

## Project structure

```
student_clearance/
├── config/
│   └── database.php       # MySQL connection (PDO)
├── database/
│   └── schema.sql        # Full DB schema + sample data
├── includes/
│   ├── header.php        # Nav + HTML head (role-based)
│   ├── footer.php        # Footer + scripts
│   └── auth.php          # Login helpers, requireRole, getCurrentUser
├── assets/
│   └── css/
│       └── style.css     # Custom styles
├── school_years/         # CRUD: School Year
├── departments/          # CRUD: Departments
├── blocks/               # CRUD: Blocks
├── teachers/             # CRUD: Teachers
├── subjects/             # CRUD: Subjects
├── requirements/         # CRUD: Requirements
├── students/             # CRUD: Students
├── student_subject/      # CRUD: Student enrollment (subjects)
├── clearance/            # CRUD: Clearance status (admin + teacher)
├── users/                # User accounts (admin only): list, add student/teacher/admin
├── login.php, logout.php
├── my_clearance.php      # Student view: own clearance only
├── index.php             # Dashboard (role-based)
└── README.md
```

## Usage

1. **Log in** as admin (default **admin** / **password**). Create **User accounts** (Master Data → User accounts) for students and teachers linked to existing student LRNs or teachers.
2. **Setup master data** (admin): School Years, Departments, Blocks, Teachers, Subjects, Requirements, Students, Enrollment.
3. **Clearance** (admin or teacher): create and update clearance records (student + requirement + verifying teacher + school year, with status and dates).
4. **Students** log in and use **My Clearance** to view their own status. **Teachers** log in to manage clearance.

This supports your paper’s goals: students can track clearance online, and personnel can verify and approve requests through the system.
