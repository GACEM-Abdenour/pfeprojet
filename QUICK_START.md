# Quick Start Guide - GIA Login System

## 🚀 Fast Setup (5 Steps)

### 1. Setup Database
```powershell
.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth
```

### 2. Insert Test Data
```powershell
php test/insert_test_data.php
```

### 3. Start Web Server
```powershell
php -S localhost:8000
```

### 4. Open Browser
```
http://localhost:8000/pages/login.php
```

### 5. Login
- **Admin:** `admin` / `admin123`
- **Technician:** `tech1` / `tech123`
- **Reporter:** `reporter1` / `user123`

---

## 📋 Test Credentials Quick Reference

| Username | Password | Role | Dashboard |
|----------|----------|------|-----------|
| admin | admin123 | Admin | admin_dashboard.php |
| tech1 | tech123 | Technician | tech_dashboard.php |
| tech2 | tech123 | Technician | tech_dashboard.php |
| reporter1 | user123 | Reporter | create_ticket.php |
| reporter2 | user123 | Reporter | create_ticket.php |
| manager1 | manager123 | Admin | admin_dashboard.php |

---

## 📚 Full Documentation

- **Test Data:** See `TEST_DATA.md` for complete test incidents and data
- **Localhost Setup:** See `RUN_LOCALHOST.md` for detailed instructions
- **Troubleshooting:** See `RUN_LOCALHOST.md` Step 5

---

## ✅ Checklist Before Running

- [ ] SQL Server Express installed and running
- [ ] PHP installed with PDO_SQLSRV extension
- [ ] Database credentials updated in `includes/db.php`
- [ ] Database created (run `setup_database.ps1`)
- [ ] Test data inserted (run `insert_test_data.php`)
- [ ] Web server started (PHP built-in or IIS)

---

**Ready to test!** 🎉
