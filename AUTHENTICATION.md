# Role-Based Authentication System

## Overview
This document describes the newly implemented role-based authentication system for the Online Food Ordering System.

## User Roles

The system now supports 4 different user roles:

### 1. **Customer** 👤
- Browse and order food from restaurants
- View order history
- Track deliveries
- Manage cart

### 2. **Restaurant Manager** 🏪
- Menu, orders, reviews, analytics
- Manage restaurant profile
- Add/edit menu categories and menu items
- Create limited-time discounts
- View live AJAX order updates
- Reply to customer reviews
- View sales analytics and restaurant complaints

### 3. **Delivery Agent** 🚗
- Accept deliveries, update status, track earnings
- Track active deliveries
- Update delivery status
- Earn delivery fees

### 4. **Platform Admin** ⚙️
- User management, platform oversight, reports
- Manage all users
- Manage restaurants
- Manage platform settings
- View system analytics

## New Features

### 1. Unified Landing Page
- **URL**: `http://localhost/Food/public/index.php?route=auth&action=unified` or `login.php`
- **Features**:
  - Role selection with visual cards
  - Automatic redirection for logged-in users
  - Responsive design for all devices

### 2. AJAX-Powered Authentication
- **No page reloads** during login/registration
- **Vanilla JavaScript** (no jQuery, React, or Node.js)
- **RESTful API endpoint**: `public/auth_ajax.php`
- **Response format**: JSON

### 3. Login & Registration Forms
- Separate forms for each role
- Client-side validation
- AJAX submission
- Real-time error messages
- Password strength validation (minimum 6 characters)
- Phone number field for delivery agents and restaurant managers

## File Structure

### New/Modified Files

```
public/
├── auth_ajax.php                 # AJAX authentication handler
├── index.php                     # Updated routing

views/
└── auth/
    └── unified.php              # Unified login/registration page

assets/
└── css/
    └── style.css               # Updated with auth styling

controllers/
└── AuthController.php           # Updated with AJAX methods

models/
└── User.php                     # Updated for new fields

config/
├── database.php                 # Database connection

data/
└── init.sql                     # Updated schema

inc/
└── functions.php               # Updated with role helpers

index.php                        # Updated to use new auth
login.php                        # Updated to redirect to new auth
```

## Database Schema Changes

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    role ENUM('customer','admin','restaurant_manager','delivery_man') NOT NULL DEFAULT 'customer',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

## API Endpoints

### Login via AJAX
**Endpoint**: `POST /public/auth_ajax.php?action=login`

**Request Body**:
```json
{
    "email": "user@example.com",
    "password": "Customer@123",
    "role": "customer"
}
```

**Success Response** (HTTP 200):
```json
{
    "success": true,
    "message": "Login successful",
    "role": "customer"
}
```

**Error Response** (HTTP 200):
```json
{
    "success": false,
    "message": "Invalid email or password"
}
```

### Register via AJAX
**Endpoint**: `POST /public/auth_ajax.php?action=register`

**Request Body**:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Customer@123",
    "confirm_password": "Customer@123",
    "phone": "+1234567890",
    "role": "customer"
}
```

**Success Response** (HTTP 200):
```json
{
    "success": true,
    "message": "Registration successful",
    "role": "customer",
    "requires_approval": false,
    "logged_in": true
}
```

For Restaurant Manager and Delivery Agent registration, the account is created as inactive and the response includes `"requires_approval": true`. Restaurant Manager registration also stores restaurant profile details for admin approval. A Platform Admin must approve those accounts before normal login. A new Platform Admin account is also inactive, but it is allowed into User Management so the admin can approve their own account.

**Error Response** (HTTP 200):
```json
{
    "success": false,
    "message": "Email is already registered"
}
```

## JavaScript Functions

### Main Functions in `views/auth/unified.php`

#### `selectRole(role)`
Switches from role selection to authentication form
- **Parameters**: `role` (customer, restaurant_manager, delivery_man, admin)

#### `backToRoles()`
Returns to role selection screen

#### `switchTab(tab)`
Switches between login and register forms
- **Parameters**: `tab` (login or register)

#### `handleLogin(event)`
Submits login form via AJAX
- Validates email and password
- Sends credentials to server
- Redirects on success

#### `handleRegister(event)`
Submits registration form via AJAX
- Validates all fields
- Checks email uniqueness on server
- Creates new user account
- Redirects on success

#### `showError(message)`
Displays error message to user

#### `clearErrors()`
Clears error messages

#### `clearForm(formId)`
Resets form fields

## Helper Functions

### New Functions in `inc/functions.php`

```php
// Check if user has restaurant_manager role
isRestaurantManager(): bool

// Check if user is a delivery agent
isDeliveryMan(): bool

// Check if user is a customer
isCustomer(): bool
```

## Security Features

1. **Password Hashing**: Uses PHP's `password_hash()` with PASSWORD_DEFAULT
2. **CSRF Protection**: Can be added via middleware (recommended for production)
3. **Input Validation**: Server-side validation on all inputs
4. **Email Validation**: RFC-compliant email validation
5. **SQL Injection Prevention**: Prepared statements with bound parameters
6. **XSS Prevention**: Input sanitization with `htmlspecialchars()`
7. **Session Management**: Secure session handling with role-based access control

## Routing

### Auth Routes
| Route | Action | Description |
|-------|--------|-------------|
| `?route=auth&action=unified` | `unified()` | Unified login/register page |
| `?route=auth` | `login()` | Legacy login page |
| `?route=auth&action=register` | `register()` | Legacy register page |
| `?route=auth&action=logout` | `logout()` | Logout user |

## Usage Example

### Accessing the Login Page
```
http://localhost/Food/public/index.php?route=auth&action=unified
```

### JavaScript Example - Custom Login
```javascript
fetch('public/auth_ajax.php?action=login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'Customer@123',
        role: 'customer'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        window.location.href = 'public/index.php?route=home';
    } else {
        console.error(data.message);
    }
});
```

## Redirects After Login

- **Customer**: `public/index.php?route=home`
- **Restaurant Manager**: `public/index.php?route=restaurant`
- **Delivery Agent**: `public/index.php?route=delivery`
- **Administrator**: `public/index.php?route=admin`

## Testing Credentials

Use the email address to log in.

| Role | Email | Password |
|------|-------|----------|
| Customer | `customer@food.local` | `Customer@123` |
| Delivery Agent | `delivery@food.local` | `Delivery@123` |
| Restaurant Manager | `manager@food.local` | `Manager@123` |
| Platform Admin | `admin@food.local` | `Admin@123` |

## Database Update

- Fresh setup: import `data/init.sql`.
- Existing setup that already imported the older SQL: import `data/restaurant_manager_update.sql` once.
- Do not run `add_setting.sql` or `add_setting_kv.sql` after the updated `init.sql`; those settings are already included.

### Test Login Flow
1. Visit `http://localhost/Food/public/index.php?route=auth&action=unified`
2. Select a role (e.g., Customer)
3. Click "Login" tab
4. Enter credentials
5. Click "Login" button
6. Should redirect to appropriate dashboard

### Test Registration Flow
1. Visit `http://localhost/Food/public/index.php?route=auth&action=unified`
2. Select a role (e.g., Restaurant Manager)
3. Click "Register" tab
4. Fill in all fields
5. Click "Create Account" button
6. Customer accounts should log in immediately. Restaurant Manager and Delivery Agent accounts should wait for admin approval. Admin registrations should open User Management so the new admin can approve their own account.

## Browser Compatibility

- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 12+
- ✅ Edge 79+
- ✅ Opera 47+

All modern browsers are supported. The system uses modern JavaScript (ES6) and CSS Grid/Flexbox.

## Responsive Design

The authentication system is fully responsive:
- **Desktop**: 2-column role selection grid
- **Tablet**: Adjusted spacing and card sizes
- **Mobile**: Single-column layout with full-width elements

## Future Enhancements

1. ✨ Add Google/Facebook OAuth login
2. ✨ Email verification on registration
3. ✨ Two-factor authentication (2FA)
4. ✨ Password recovery/reset
5. ✨ Social login integration
6. ✨ CSRF token implementation
7. ✨ Rate limiting on login attempts
8. ✨ User profile management

## Troubleshooting

### "Invalid email or password" for correct credentials
- Ensure the user role matches the selected role in the UI
- Check that the user exists in the database
- Verify password is correct (passwords are case-sensitive)

### Forms not submitting
- Check browser console for JavaScript errors
- Ensure cookies are enabled
- Verify auth_ajax.php file exists in public folder
- Check that PHP JSON extension is enabled

### Redirects not working
- Clear browser cache
- Check that location URLs are correct
- Verify session is being created
- Check browser developer tools for any AJAX errors

### Styling issues
- Clear browser cache
- Verify style.css is loading (check Network tab in DevTools)
- Check for CSS conflicts with other stylesheets
- Ensure viewport meta tag is present

## Support

For issues or questions, please refer to the main README.md file or contact the development team.

---
**Last Updated**: May 14, 2026
**Version**: 2.0 (Role-Based Authentication)
