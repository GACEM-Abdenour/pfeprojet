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
All demo accounts use the same password: **`password`**.

- **Admin:** `admin`
- **Technicians:** `tech1`, `tech2`
- **Reporters:** `reporter1`, `reporter2`

---

## 📋 Test Credentials Quick Reference

| Username | Password | Role | Dashboard |
|----------|----------|------|-----------|
| admin | password | Admin | admin_dashboard.php |
| tech1 | password | Technician | tech_dashboard.php |
| tech2 | password | Technician | tech_dashboard.php |
| reporter1 | password | Reporter | create_ticket.php |
| reporter2 | password | Reporter | create_ticket.php |

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
