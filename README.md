# ClinicDesk — Quick Start Guide

## 1. Copy Files
Place the `clinicdesk` folder inside:
- **XAMPP**: `C:/xampp/htdocs/clinicdesk`
- **WAMP**:  `C:/wamp64/www/clinicdesk`
- **Linux**: `/var/www/html/clinicdesk`

## 2. Create the Database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **Import** → choose `clinicdesk_db.sql` → click **Go**

## 3. Edit Database Config
Open `config/database.php` and change if needed:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'clinicdesk_db');
define('DB_USER', 'root');
define('DB_PASS', '');        // your MySQL password
```

## 4. Edit App URL
Open `config/config.php` and set:
```php
define('BASE_URL', 'http://localhost/clinicdesk');
```

## 5. Open the App
Visit: `http://localhost/clinicdesk`

## Default Admin Login
| Field    | Value                |
|----------|----------------------|
| Email    | admin@clinic.local   |
| Password | Admin@1234           |

## Folder Permissions (Linux/Mac only)
```bash
chmod -R 755 public/uploads/
```

---
## Project Structure
```
clinicdesk/
├── config/          Database & app settings
├── core/            Database, Auth, CSRF, Paginator, helpers
├── models/          UserModel, DoctorModel, AppointmentModel, etc.
├── controllers/     Business logic per feature
├── views/           HTML templates per feature
│   ├── partials/    Shared: header, sidebar, footer, alerts
│   ├── auth/        Login, change password
│   ├── dashboard/   Role-based dashboards
│   ├── users/       Admin: user management
│   ├── doctors/     Doctor list & profile
│   ├── appointments/Book, view, schedule, detail
│   ├── prescriptions/Add & view prescriptions
│   ├── reports/     Admin reports + CSV export
│   └── errors/      403, 404
├── public/uploads/  Avatar, doctor photo, prescription PDF uploads
├── index.php        Front controller (router)
└── clinicdesk_db.sql Database schema + seed data
```
