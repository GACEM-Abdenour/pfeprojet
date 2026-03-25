## GIA Incident Platform – Guide for CS Students

This document explains the project in simple terms, so that a beginner computer science student can understand how it works and how the files fit together.

---

### 1. What this project does

Think of this application as an online helpdesk for a company:

- **Employees (Reporters)** create *incident tickets* when something is broken (printer down, no network, no access, etc.).
- **Technicians** see a list of tickets, take ownership of them, update the *status* (Open → Assigned → Diagnostic → Resolved → Closed / Failed), and write comments about what they did.
- **Admins (Managers)** see statistics about all tickets (how many, which status, etc.).

Everything is stored in a **SQL Server database**, and the web pages are written in **plain PHP** with **Bootstrap** for the visual layout.

---

### 2. How a web request flows (high level)

When you open a page in your browser, this roughly happens:

1. The browser asks the PHP server for a `.php` file (for example `pages/login.php`).
2. PHP runs the code inside that file:
   - It may **read from the database** (for example, to show your tickets).
   - It may **write to the database** (for example, when you submit a form).
3. PHP generates **HTML**, which is sent back to the browser.
4. The browser shows that HTML to the user.

When you submit a form (for example, “Create Ticket”):

1. The form sends a **POST request** to an **action file** in `/actions/`.
2. The action file:
   - Reads `$_POST` (and `$_FILES` for uploads).
   - Talks to the database.
   - Updates or inserts rows.
   - Then sends `header("Location: ...")` to redirect you back to a page.

---

### 3. Project structure you must remember

We use a **strict directory structure** to keep things organized:

- **`/pages/` – Visual pages**
  - These are the files the browser opens directly.
  - They mainly contain **HTML + some PHP** to *display* data.
  - Examples:
    - `login.php` – Login form.
    - `reporter_dashboard.php` – “My Tickets” for the reporter.
    - `create_ticket.php` – Form to create a new ticket.
    - `tech_dashboard.php` – Technician’s ticket lists.
    - `admin_dashboard.php` – Admin view with statistics.
    - `view_ticket.php` – Detailed view of one ticket and its history.

- **`/actions/` – Form handlers (no HTML here)**
  - These files **process forms** and **never print HTML**.
  - They:
    - Validate input.
    - Update the database.
    - Redirect back to a `/pages/` file.
  - Examples:
    - `login_action.php` – Handles login form, sets the session, redirects to the right dashboard.
    - `logout_action.php` – Destroys the session and redirects to `login.php`.
    - `submit_ticket.php` – Handles “Create Ticket” form, inserts into `incidents`, logs creation, then redirects to `reporter_dashboard.php`.
    - `update_ticket.php` – Used by technicians to change ticket status and add comments.

- **`/includes/` – Reusable building blocks**
  - Shared code used by many pages.
  - Examples:
    - `db.php` – Creates a **PDO connection** to SQL Server.
    - `functions.php` – Helper functions:
      - `requireLogin()`, `requireRole()` (access control).
      - `getCurrentUserId()`, `getCurrentUserRole()`.
      - `logIncidentAction()` to add rows to `incident_logs`.
      - Small helpers like `escape()` and `formatDateTime()`.
    - `sidebar.php` – Left navigation menu that changes based on role.
    - `header.php` – Top bar showing the logged-in user and role.

- **Other important folders**
  - `/database/` – SQL script for creating tables.
  - `/test/` – Scripts and docs for inserting test data and validating code.
  - `/src/assets/` – CSS, JS, and images (template theme).
  - `/uploads/` – Where uploaded files (screenshots, logs) are stored.

---

### 4. The database model (simplified)

The database has four main tables:

- **`users`**
  - Stores login accounts.
  - Important columns: `username`, `password_hash`, `role` (Reporter, Technician, Admin).

- **`incidents`**
  - One row = one ticket.
  - Linked to a reporter (`user_id`) and optionally to a technician (`assigned_to`).
  - Has `title`, `description`, `category`, `priority`, `status`, timestamps.

- **`attachments`**
  - Files attached to an incident (e.g., screenshot).
  - Contains the file path on disk and the original name.

- **`incident_logs`**
  - History of what happened to a ticket.
  - Examples of `action_type`: `Creation`, `Status Change`, `Comment`, `Assignment`.

This design is **relational**: tables are connected by **foreign keys**, so you can join them in SQL queries.

---

### 5. Typical user flows (step by step)

#### 5.1 Reporter flow

1. Reporter logs in.
2. They are redirected to `reporter_dashboard.php`.
3. From there they can:
   - See **“My Tickets”** (rows from `incidents` where `user_id` = their id).
   - Click **“Nouveau ticket”** to go to `create_ticket.php`.
4. On `create_ticket.php` they fill in a form:
   - Title, category, priority, description, optional attachment.
5. The form sends a POST request to `actions/submit_ticket.php`.
6. `submit_ticket.php`:
   - Validates the input.
   - Inserts a new row in `incidents` with status `Open`.
   - Optionally saves an attachment into `/uploads` and `attachments`.
   - Adds a `"Creation"` entry to `incident_logs`.
   - Redirects back to `reporter_dashboard.php` with a success message.

#### 5.2 Technician flow

1. Technician logs in → redirected to `tech_dashboard.php`.
2. `tech_dashboard.php` shows:
   - **“Mes tickets assignés”** (where `assigned_to` = this technician).
   - **“Tickets non assignés”** (where `assigned_to` is `NULL`).
3. Clicking on a ticket title opens `view_ticket.php?id=X`.
4. `view_ticket.php`:
   - Shows all details, attachments, and the log history.
   - If the logged-in user is a technician, it shows a form to:
     - Change the `status` (e.g., Diagnostic, Resolved, Closed, Failed/Blocked).
     - Add a comment.
5. That form posts to `actions/update_ticket.php`.
6. `update_ticket.php`:
   - Updates the `incidents` row.
   - If the ticket was unassigned, it assigns it to the current technician.
   - Adds log entries to `incident_logs` (for status changes and comments).
   - Redirects back to `view_ticket.php?id=X`.

#### 5.3 Admin flow

1. Admin logs in → redirected to `admin_dashboard.php`.
2. `admin_dashboard.php`:
   - Shows **KPI cards**:
     - Total tickets.
     - Pending tickets (Open + Assigned + Diagnostic).
     - Resolved/Closed tickets.
   - Shows a **Chart.js pie chart** of ticket counts per status.
   - Shows a table of all tickets; each title links to `view_ticket.php?id=X`.

---

### 6. Understanding sessions and roles

When you log in successfully, the app sets **session variables** like:

- `$_SESSION['user_id']`
- `$_SESSION['username']`
- `$_SESSION['role']`

Every protected page starts with something like:

- `session_start();`
- `require_once '../includes/functions.php';`
- `requireRole('Technician');` or `requireRole('Admin');` or `requireRole('Reporter');`

If the role doesn’t match, the user is redirected back to the login page with an error message.  
This is how we implement **role-based access control** purely in PHP, without any framework.

---

### 7. Which files are mostly legacy / advanced

You may see some files that are **not used directly anymore** in the main flow but are kept as technical references:

- **Legacy/auth helpers (older style, now replaced by `/actions`):**
  - `includes/auth.php` – Old login handler, logic now lives in `actions/login_action.php`.
  - `includes/logout.php` – Old logout handler, replaced by `actions/logout_action.php`.
  - `includes/create_ticket.php` – Earlier version of ticket creation logic, now replaced by `actions/submit_ticket.php`.

- **Template README (not specific to GIA):**
  - `README.md` – Comes from the NiceAdmin/MaterialM template and talks about the generic UI template, not about the GIA incident app itself.

- **Very detailed technical docs (good for deeper study, not needed for first contact):**
  - `TECHNICAL_REPORT.md` – Very long, formal technical report.
  - `RUN_LOCALHOST.md` – Full localhost setup with many options.
  - `SQL_SERVER_SETUP.md`, `CONNECTION_STRINGS.md`, `FIX_CONNECTION_ERROR.md`, `INSTALL_PDO_SQLSRV.md` – Focused on Windows / SQL Server / PHP driver setup and troubleshooting.

For a **beginner**, you can mostly stick to:

- `GIA_STUDENT_GUIDE.md` (this file).
- `context.md` (project master plan).
- `TEST_DATA.md` (what test users and tickets exist).
- `QUICK_START.md` (short “how to run it” commands).

You can read the other docs later when you want to understand **deployment details** and **troubleshooting**.

---

### 8. How to run the project (short version)

Full details are in `RUN_LOCALHOST.md` and `QUICK_START.md`. Very short version:

1. **Prepare the database**
   - Install SQL Server Express.
   - Run `setup_database.ps1` to create the database and tables.
   - Run `php test/insert_test_data.php` (or `test/insert_test_data.sql` in SSMS) to insert sample users and tickets.

2. **Start the PHP built-in server**
   - In a terminal, from the project folder:
     ```powershell
     php -S localhost:8000
     ```

3. **Open the login page**
   - Visit `http://localhost:8000/pages/login.php`.

4. **Use test accounts**
   - Look at `TEST_DATA.md` or `QUICK_START.md` for usernames; all listed accounts use the password `password`.

---

### 9. How to explore the code as a student

If you’re learning, a good order to read the code is:

1. **`pages/login.php`** – See how a login form is built with HTML + Bootstrap.
2. **`actions/login_action.php`** – See how form data is checked against the database and how sessions are created.
3. **`includes/db.php`** – Learn how PDO connects to SQL Server.
4. **`includes/functions.php`** – See helper functions for authentication and logging.
5. **`pages/create_ticket.php`** and **`actions/submit_ticket.php`** – Understand how new data is inserted.
6. **`pages/tech_dashboard.php`** and **`pages/view_ticket.php`** – Understand how we read and display data, and how technicians update tickets.
7. **`pages/admin_dashboard.php`** – See a simple example of statistics and a Chart.js pie chart.

Try to follow one **complete scenario**, for example:

> “Reporter creates a ticket → Technician updates it → Admin sees it in statistics”

and track which PHP files are involved at each step.

---

### 10. Summary

- The project is a **ticket/incident management web app** using **PHP + SQL Server**.
- It is organized into **`pages/` (views)**, **`actions/` (form handlers)**, and **`includes/` (shared code + layout)**.
- The database tables (`users`, `incidents`, `attachments`, `incident_logs`) model users, tickets, files, and history.
- Different roles (Reporter, Technician, Admin) see different dashboards, but they all use the **same database** underneath.
- Many technical markdown files exist, but as a beginner you mainly need:
  - This guide,
  - The master plan in `context.md`,
  - The quick start and test data docs.

Once you are comfortable with this high-level view, you can dive into the more advanced documents to learn about deployment, drivers, and production concerns.

