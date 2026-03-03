# Separated Login System - Setup Guide

## Overview
The login system has been split into two separate systems for better security:

1. **Customer Login** (`login.php`) → Customer Portal (`home.php`)
2. **Admin Login** (`admin_login.php`) → Admin Dashboard (`admin.php`)

## Files Modified/Created

### New Files:
- **`admin_login.php`** - Admin-only login page with professional styling
- **`admin_auth_process.php`** - Dedicated admin authentication handler

### Updated Files:
- **`login.php`** - Customer-only login page with link to admin portal
- **`auth_process.php`** - Simplified to handle only customer authentication
- **`session_check.php`** - Enhanced with admin-only verification and session timeout

## Database Setup

### Option 1: Create Admin Table (Recommended)
Create a dedicated `admins` table for better security:

```sql
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT DEFAULT 1
);
```

### Insert Admin User (Hashed Password):
```sql
-- Password: admin123 (hashed with bcrypt)
INSERT INTO admins (username, password, email, is_active) VALUES 
('admin', '$2y$10$9IQmtPgJ4QEYYf.M.2h5SOYk2xJ.B.L.p7t7A.1n.2q5gXnYmXyti', 'admin@byahero.com', 1);
```

### If Using Legacy System:
If you don't create the `admins` table, the system will default to the hardcoded credentials:
- **Username:** `admin`
- **Password:** `admin123`

## How It Works

### Customer Flow:
1. User visits `login.php`
2. Enters customer credentials
3. Authenticated via `auth_process.php`
4. Redirected to `home.php` with role = 'customer'
5. Link to `admin_login.php` in footer for admins

### Admin Flow:
1. Admin visits `admin_login.php`
2. Enters admin credentials
3. Authenticated via `admin_auth_process.php`
4. Redirected to `admin.php` with role = 'admin'
5. Session automatically checked and enforced

## Security Features

✅ **Separated Authentication:** Admin and customer credentials are verified separately
✅ **Role-Based Access Control:** Only admins can access admin pages
✅ **Session Timeout:** Admin sessions timeout after 30 minutes (configurable)
✅ **Expired Session Handling:** Redirects to admin login with expiration notice
✅ **Password Hashing:** Supports bcrypt hashing (recommended)

## Configuration

### Adjust Session Timeout (in `session_check.php`):
```php
$timeout = 1800; // Change to desired seconds (1800 = 30 minutes)
```

### Add More Admins:
```sql
-- Add new admin (generate password hash at https://www.bcryptgenerator.com/)
INSERT INTO admins (username, password, email, is_active) VALUES 
('superadmin', '$2y$10$[HASHED_PASSWORD_HERE]', 'superadmin@byahero.com', 1);
```

## Testing

### Customer Login Test:
- Navigate to: `http://localhost/TNVS/login.php`
- Expected: Customer login page with "Customer Login" subtitle

### Admin Login Test:
- Navigate to: `http://localhost/TNVS/admin_login.php`
- Expected: Admin portal page with "Admin Portal" title and admin badge
- Use credentials: admin / admin123 (or your database credentials)

## Migration Notes

If you want to migrate existing admin users from the `users` table:

```sql
-- Copy existing admins to new admins table
INSERT INTO admins (username, password, email, is_active)
SELECT username, password, email, 1 FROM users WHERE username = 'admin';
```

## Security Recommendations

1. **Change default password** immediately in production
2. **Use strong passwords** for all admin accounts
3. **Hash passwords** using bcrypt (use `password_hash()` in PHP)
4. **Enable HTTPS** for login pages in production
5. **Add 2FA** for additional security (future enhancement)
6. **Log admin activities** for audit trails (future enhancement)

---

For any issues, check that admins table exists or credentials are correct in hardcoded fallback.
