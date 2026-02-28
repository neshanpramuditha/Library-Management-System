# 📚 Library Appointment & Book Donation System

*A Web-Based Information System for Efficient Library Scheduling and Donation Management*

---

## 📌 Overview

The **Library Appointment and Book Donation System** is a full-stack web application built to modernize and streamline core library operations. It automates:

* Appointment scheduling
* Book donation submission and verification
* User access control
* Reporting and audit logging

This mini-project was developed as part of **SWST 31032 – Applied Information Systems**, applying key IS concepts such as Transaction Processing Systems (TPS), Database Management Systems (DBMS), Role-Based Access Control (RBAC), and secure information handling.

---

## 🎯 Key Features

### 🔐 User Management

* Secure login & registration
* Role-Based Access Control (Admin, Librarian, Patron)
* Profile management
* Password recovery

### 🗓️ Appointment Management

* Real-time availability calendar
* Book/modify/cancel appointments
* Prevents double bookings (TPS-based)
* Automated email confirmations
* Appointment history tracking

### 📘 Book Donation Module

* Online donation form with optional image upload
* Staff verification workflow (approve/reject)
* Donation history & donor statistics

### 🛠️ Admin Panel

* User account management
* Appointment type configuration
* Time slot blocking
* Audit logs
* Reporting & analytics (appointments, donations, users)

### 🔒 Security Features

* Bcrypt password hashing
* SQL Injection prevention (prepared statements)
* XSS & CSRF protection
* Session security + audit trails

---

## 🏛️ System Architecture

* **Architecture Pattern:** MVC
* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP (PDO)
* **Database:** MySQL
* **Server:** Apache
* **Tools:** XAMPP, VS Code

Directory structure, ERD, and system flows are documented inside the `/docs` section (if added).

---

## 🗃️ Database Design

* Designed in **3rd Normal Form (3NF)**
* Key entities:

  * Users
  * Appointments
  * Appointment Types
  * Donations
  * Audit Logs

Relationships:

* User ↔ Appointments (1-to-Many)
* User ↔ Donations (1-to-Many)
* Appointment Type ↔ Appointments (1-to-Many)

---

## 📅 Timeline (3 Weeks)

### Week 1

* Requirement gathering
* UI mockups
* Database design
* Authentication module

### Week 2

* Complete appointment system
* Donation management module

### Week 3

* Frontend integration
* Testing & bug fixes
* Documentation & presentation

---

## 🚀 Expected Outcomes

### Delivered

✔ Fully functional web system
✔ Real-time appointment booking
✔ Book donation management workflow
✔ Admin reporting & dashboards
✔ Security protections
✔ Full documentation + project report

### Learning Outcomes

* Understanding TPS, DBMS & RBAC
* Full-stack development workflow
* Security-aware coding
* Project planning & SDLC application

---

## 📈 System Benefits

### For Libraries

* 70% reduction in scheduling workload
* Better donor engagement
* Improved resource utilization

### For Patrons

* 24/7 accessibility
* Easy online booking
* Simple donation tracking

### For Management

* Reports & analytics
* Reliable audit trails
* Enhanced decision making

---

## 🔮 Future Enhancements

* SMS notifications (Twilio)
* Mobile application version
* Google/Outlook calendar sync
* Advanced analytics + dashboards
* Multi-branch library support
* Integration with catalog systems

---

## 📂 Project Links

*(Add links when available)*

* **GitHub Repository:** *https://github.com/neshanpramuditha/Book-Story-A-libary-booking-system-.git*
* **Project Presentation Video:** *https://youtu.be/NbcktSbL-nQ*

---

## 📖 References

* SWST 31032 – Applied Information Systems (Lecture Notes, 2025)
* PHP Documentation — php.net
* MySQL 8.0 Reference Manual
* W3Schools Web Development Tutorials
* Laudon & Laudon, *Management Information Systems* (15th Edition)

---

## 🧪 Sample Code Snippets

### `database.php`

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    // ...
}
```

### `logout.php`

```php
session_start();

if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    // Log user logout...
}
session_destroy();
```

---

