# Online Food Ordering System

A multi-role food ordering web app built with plain PHP and MySQL, designed to run on XAMPP.

User Roles
1. Customer 👤

Browse restaurants and menu items
Add items to cart and place orders
View order history and track deliveries
Leave reviews on completed orders
Account status on registration: active (can log in immediately)

2. Restaurant Manager 🏪

Manage restaurant profile (name, logo, hours, delivery radius)
Add, edit, and delete menu categories and items
Create limited-time discount offers
View and update incoming orders (live AJAX feed)
Reply to customer reviews
View sales analytics and complaints dashboard
Account status on registration: inactive (requires admin approval)

3. Delivery Agent 🚗

View and accept delivery assignments
Update delivery status in real time
Track earnings history
Manage vehicle type and profile picture
Account status on registration: inactive (requires admin approval)

4. Platform Admin ⚙️

Approve or deactivate any user account
Approve or reject restaurant applications
Manage platform-wide settings and categories
View full analytics and complaint reports
Feature specific restaurants on the homepage
Account status on registration: inactive (can self-approve via User Management)

# File Structure

Online-Food-Ordering-System/
│
├── public/
│   ├── index.php               # Front controller / router
│   └── auth_ajax.php           # AJAX authentication endpoint (POST only)
│
├── controllers/
│   └── AuthController.php      # All auth logic: login, register, logout,
│                               #   unified, ajaxLogin, ajaxRegister
│
├── models/
│   └── User.php                # findByEmail, create, findById,
│                               #   updateStatus, updateAvailability,
│                               #   updateDeliveryProfile
│
├── views/
│   └── auth/
│       ├── unified.php         # Unified role-selection + login/register UI
│       ├── login.php           # Legacy login view (form-based fallback)
│       └── register.php        # Legacy register view (form-based fallback)
│
├── inc/
│   └── functions.php           # Session helpers, role guards, cart helpers
│
├── config/
│   └── database.php            # MySQLi connection + db_query / db_fetch_* helpers
│
├── data/
│   └── init.sql                # Full schema + seed data (all 4 default accounts)
│
├── assets/
│   └── css/
│       └── style.css           # Includes .auth-container, .role-card,
│                               #   .input-with-toggle, .password-strength styles
│
├── AUTHENTICATION.md           # ← This file
└── README.md

# Installation

Copy project to C:\xampp\htdocs\Online-Food-Ordering-System\
Start Apache and MySQL in XAMPP Control Panel
Open phpMyAdmin → create database online_food_ordering
Import data/init.sql into that database
Open the app:
http://localhost/Online-Food-Ordering-System/public/index.php?route=auth&action=unified

Database credentials are in config/database.php (default: host 127.0.0.1, user root, no password).

# Default Credentials
Use the email address to log in.

| Role | Email | Password |
|------|-------|----------|
| Customer | `customer@food.local` | `Customer@123` |
| Delivery Agent | `delivery@food.local` | `Delivery@123` |
| Restaurant Manager | `manager@food.local` | `Manager@123` |
| Platform Admin | `admin@food.local` | `Admin@123` |

# Notes
- This project uses plain PHP and procedural MySQL access.
- For production, enable secure password storage, HTTPS, and validation.
