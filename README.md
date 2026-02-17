i3+6# EduManager – Student Payment Management System

EduManager is a professional, scalable, and secure student payment management system designed for educational institutions. It provides a centralized platform to manage student records, track tuition fees, generate automated receipts, and gain financial insights through advanced reporting.

---

## 🚀 Key Features

### 📊 Smart Dashboard
- **Financial Metrics**: Real-time tracking of total students, monthly income, and late payments.
- **Recent Activity**: Quick view of latest payments and newly registered students.
- **Business Logic**: Decoupled helper functions for accurate data aggregation.

### 👥 Student Management
- **Detailed Profiles**: Comprehensive view for each student including billing status (Total Paid vs. Remaining Balance).
- **Soft Delete (Archiving)**: Safely hide students from active lists without losing historical billing data.
- **Search**: Instant lookup by name or phone number.

### 💰 Payment Lifecycle
- **Automated Receipts**: Sequential receipt numbering (e.g., `RCP-202602-0001`) generated on every transaction.
- **Status Tracking**: Intelligent payment badges (Paid / Pending / Late) based on due dates and monthly cycles.
- **Management**: Full CRUD operations for payment records with secure audit trails.

### 📈 Reporting & Export
- **Financial Trends**: 6-month revenue breakdown and collection summaries.
- **Overdue Identification**: Automated list of active students missing payments for the current month.
- **CSV Export**: High-quality data export for Students and Payments records.

### 🔒 Enterprise Security
- **Authentication**: Secure login system with `password_hash()` and session-based access control.
- **CSRF Protection**: Token-based protection on all state-changing actions.
- **Brute Force Mitigation**: Rate-limiting login attempts with timed lockouts.
- **Session Security**: Session ID regeneration on login and secure cookie parameters (HttpOnly, SameSite).

---

## 🛠 Tech Stack
- **Backend**: Pure PHP 8.x
- **Database**: MySQL (PDO for secure, prepared statements)
- **Frontend**: Vanilla HTML5, CSS3 (Custom Design System), JavaScript (ES6+)
- **Architecture**: Professional Procedural-Modular (No bloated frameworks)

---

## 📂 Project Structure

```text
├── actions/            # Server-side logic (Add, Edit, Delete, Export, Auth)
├── assets/             # Global assets (CSS, JS, Images)
├── config/             # Configuration (Database connection)
├── includes/           # Reusable components (Header, Sidebar, Helpers)
├── migrations/         # Database schema updates
├── pages/              # User-facing interface pages
└── database.sql        # Initial database schema
```

---

## ⚙️ Setup Instructions

### 1. Requirements
- XAMPP / WAMP / MAMP (PHP 7.4 or higher)
- MySQL Database

### 2. Database Configuration
1. Create a database named `student_payment_system`.
2. Import `database.sql` into your MySQL server.
3. Update `config/database.php` with your credentials:
   ```php
   $host = '127.0.0.1';
   $username = 'your_username';
   $password = 'your_password';
   ```

### 3. Default Login
- **Username**: `admin`
- **Password**: `admin123` (Note: It is recommended to change this in the Settings page immediately after login).

---

## 🛡 Security Standards
- **XSS Prevention**: All data output escaped via `htmlspecialchars()`.
- **SQLi Protection**: Mandatory use of PDO Prepared Statements.
- **CSRF Defense**: Unique tokens per session for all forms.
- **Session Hygiene**: Automatic regeneration of session IDs and strict cookie flags.

---

## 📝 License
This project is developed as a professional school management tool. Customization and modification are permitted for educational and commercial purposes.
