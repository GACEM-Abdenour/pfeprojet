# Login Flow Test Plan

## Prerequisites
1. SQL Server Express is installed and running
2. Database `GIA_IncidentDB` is created (run `setup_database.ps1`)
3. Test users are created (run `create_test_user.php`)

## Test Cases

### Test 1: Access Login Page
**URL:** `http://localhost/pages/login.php` (or your server path)

**Expected:**
- Login form displays with NiceAdmin styling
- French labels: "Nom d'utilisateur", "Mot de passe"
- "Se connecter" button
- Form posts to `../includes/auth.php`

**Test:**
- [ ] Page loads without errors
- [ ] CSS styles are applied (check browser console)
- [ ] Form is properly formatted
- [ ] All form fields are visible

---

### Test 2: Invalid Login Attempt
**Action:** Submit form with wrong credentials

**Test Data:**
- Username: `wronguser`
- Password: `wrongpass`

**Expected:**
- Redirect back to login page
- Error message displayed: "Nom d'utilisateur ou mot de passe incorrect"
- Form fields cleared

**Test:**
- [ ] Error message appears
- [ ] No session is created
- [ ] URL contains error parameter

---

### Test 3: Empty Fields Validation
**Action:** Submit form with empty username or password

**Expected:**
- HTML5 validation prevents submission (required attribute)
- OR server-side validation shows error

**Test:**
- [ ] Browser prevents empty submission
- [ ] Error message if server-side validation triggers

---

### Test 4: Admin Login
**Test Data:**
- Username: `admin`
- Password: `admin123`

**Expected:**
- Successful login
- Session created with user data
- Redirect to `admin_dashboard.php`
- Session variables: user_id, username, email, role='Admin', department

**Test:**
- [ ] Redirects to admin dashboard
- [ ] Session contains correct data
- [ ] Can access admin-only pages

---

### Test 5: Technician Login
**Test Data:**
- Username: `tech1`
- Password: `tech123`

**Expected:**
- Successful login
- Redirect to `tech_dashboard.php`
- Role-based access works

**Test:**
- [ ] Redirects to tech dashboard
- [ ] Cannot access admin pages (if implemented)

---

### Test 6: Reporter Login
**Test Data:**
- Username: `reporter1`
- Password: `user123`

**Expected:**
- Successful login
- Redirect to `create_ticket.php`
- Can create tickets

**Test:**
- [ ] Redirects to ticket creation page
- [ ] Cannot access admin/tech dashboards (if implemented)

---

### Test 7: Already Logged In Redirect
**Action:** Access login page while already logged in

**Expected:**
- Automatic redirect to appropriate dashboard based on role
- No login form shown

**Test:**
- [ ] Admin → admin_dashboard.php
- [ ] Technician → tech_dashboard.php
- [ ] Reporter → create_ticket.php

---

### Test 8: Remember Me Functionality
**Action:** Login with "Se souvenir de moi" checked

**Expected:**
- Cookie `gia_remember` is set
- Cookie expires in 30 days
- Cookie is HttpOnly and Secure (if HTTPS)

**Test:**
- [ ] Cookie is created
- [ ] Cookie contains encoded user data
- [ ] Cookie has correct expiration

---

### Test 9: Session Security
**Action:** After login, check session

**Expected:**
- Session ID is regenerated (session_regenerate_id)
- Session contains only necessary data
- No sensitive data in session (password_hash not stored)

**Test:**
- [ ] Session ID changes after login
- [ ] No password_hash in session
- [ ] Session data is correct

---

### Test 10: SQL Injection Protection
**Test Data:**
- Username: `admin' OR '1'='1`
- Password: `anything`

**Expected:**
- Login fails (prepared statement prevents injection)
- Error message shown
- No user data exposed

**Test:**
- [ ] Login fails
- [ ] No SQL errors exposed
- [ ] Database remains secure

---

### Test 11: XSS Protection
**Test Data:**
- Username: `<script>alert('XSS')</script>`
- Password: `test`

**Expected:**
- Input is sanitized
- No script execution
- Safe error message display

**Test:**
- [ ] No script execution
- [ ] HTML is escaped in error messages
- [ ] Form values are sanitized

---

## Manual Testing Checklist

1. **Database Connection**
   - [ ] Update `includes/db.php` with correct credentials
   - [ ] Test connection: `php -r "require 'includes/db.php'; var_dump(getDBConnection());"`

2. **Database Setup**
   - [ ] Run `setup_database.ps1` successfully
   - [ ] Verify tables exist: `SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES`

3. **Test Users**
   - [ ] Run `create_test_user.php`
   - [ ] Verify users exist: `SELECT username, role FROM users`

4. **Web Server**
   - [ ] PHP is configured
   - [ ] PDO_SQLSRV extension is installed
   - [ ] Session directory is writable
   - [ ] Error reporting is configured (for development)

5. **Browser Testing**
   - [ ] Test in Chrome/Firefox/Edge
   - [ ] Check browser console for errors
   - [ ] Verify responsive design on mobile

---

## Expected File Structure

```
sonalgaz/
├── includes/
│   ├── db.php          ✅ Database connection
│   ├── auth.php        ✅ Authentication handler
│   ├── functions.php   ✅ Helper functions
│   └── logout.php      ✅ Logout handler
├── pages/
│   └── login.php       ✅ Login page
├── index.php           ✅ Router
└── test/
    ├── create_test_user.php  ✅ Test user creation
    └── validate_code.php     ✅ Code validation
```

---

## Troubleshooting

### Database Connection Error
- Check SQL Server is running: `Get-Service | Where-Object {$_.DisplayName -like "*SQL*"}`
- Verify credentials in `db.php`
- Check PDO_SQLSRV extension: `php -m | grep sqlsrv`

### Session Not Working
- Check `php.ini`: `session.save_path` is writable
- Verify `session_start()` is called before headers
- Check browser cookies are enabled

### Redirect Not Working
- Ensure no output before `header()` calls
- Check for whitespace before `<?php` tags
- Verify relative paths are correct

### CSS/JS Not Loading
- Check file paths in `login.php`
- Verify `src/assets/` directory exists
- Check web server document root configuration
