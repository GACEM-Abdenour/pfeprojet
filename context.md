# MASTER IMPLEMENTATION PLAN: GIA Incident Platform

## 1. Architectural Rules (STRICT)

To avoid "404 Not Found" errors and spaghetti code, this project MUST follow a strict directory structure.

- **`/pages/`** : Only visual UI files (HTML/Bootstrap + PHP data fetching).
- **`/actions/`** : Only form-processing logic. These files process `$_POST` data, interact with the DB, and `header("Location: ...")` redirect back to a page. **NO HTML output here.**
- **`/includes/`** : Only reusable components (`db.php`, `functions.php`, `header.php`, `sidebar.php`).

---

## 2. Directory Structure to Enforce

```text
/sonalgaz
|-- /actions
|   |-- login_action.php
|   |-- logout_action.php
|   |-- submit_ticket.php (Handles Reporter creation)
|   |-- update_ticket.php (Handles Tech status changes/comments)
|-- /includes
|   |-- db.php
|   |-- functions.php
|   |-- header.php (NiceAdmin Topbar)
|   |-- sidebar.php (Dynamic based on Role)
|-- /pages
|   |-- login.php
|   |-- reporter_dashboard.php (My Tickets)
|   |-- create_ticket.php (Form)
|   |-- tech_dashboard.php (My Queue & Unassigned Pool)
|   |-- admin_dashboard.php (Statistics & Global View)
|   |-- view_ticket.php (Crucial: Shows details, logs, and update form)
|-- /uploads (Secure attachment storage)
```

---

## 3. Dynamic Navigation (`includes/sidebar.php`)

The sidebar must adapt based on `$_SESSION['role']`.

- **Reporter**:  
  - Sees "My Tickets" (`reporter_dashboard.php`)  
  - Sees "New Ticket" (`create_ticket.php`)

- **Technician**:  
  - Sees "Tech Dashboard" (`tech_dashboard.php`)  
  - Sees "All Tickets"

- **Admin**:  
  - Sees "Admin Dashboard" (`admin_dashboard.php`)  
  - Sees "Manage Users"

---

## 4. Missing Core Workflows to Implement

### A. The Reporter Flow

- `pages/reporter_dashboard.php`:  
  The reporter needs a dashboard to see the tickets they previously opened.  
  Show a table of their tickets with current **Status**.

- `pages/create_ticket.php`:  
  Form submits `POST` data to `actions/submit_ticket.php`.

- `actions/submit_ticket.php`:  
  - Validates input  
  - Uploads file to `/uploads/`  
  - Inserts to `incidents`  
  - Inserts `"Creation"` to `incident_logs`  
  - Redirects to `reporter_dashboard.php`

---

### B. The Technician Flow (The Missing Logic)

- `pages/tech_dashboard.php`:  
  Shows two tables:
  - "Unassigned Tickets"
  - "My Assigned Tickets"

- **Clicking a Ticket**:  
  Clicking any ticket row MUST link to:  
  `pages/view_ticket.php?id=X`

#### `pages/view_ticket.php` (CRITICAL MISSING PAGE)

- Fetches ticket details + user info + attachments
- Fetches the history from `incident_logs`
- Displays logs as a timeline
- If user is a **Tech**:
  - Shows a form to change Status (e.g., `Diagnostic`, `Resolved`)
  - Allows adding a comment
  - Form submits to `actions/update_ticket.php`

#### `actions/update_ticket.php`

- Updates `incidents` table
- Adds new comment/action to `incident_logs`
- Redirects back to `view_ticket.php?id=X`

---

### C. The Admin Flow (Statistics)

- `pages/admin_dashboard.php`:
  - Do NOT just show a table
  - Include 3 KPI Cards at the top:
    - Total Tickets
    - Pending Tickets
    - Resolved Tickets

- **Chart.js**:
  - Add a pie chart showing tickets by Status

---

## 5. Implementation Execution

Cursor MUST:

- Read this file and systematically audit the current codebase.
- Move misaligned files (like `includes/create_ticket.php`) into the `/actions/` folder.
- Rename them to standard action handlers.
- Build the missing:
  - `pages/view_ticket.php`
  - `actions/update_ticket.php`
- Consolidate UI elements into `includes/sidebar.php` so all pages share the same navigation.

---

# Step 2: The Prompt to Feed Cursor

Once you have saved that file, open Cursor's chat (make sure it's indexing your codebase) and paste this exact prompt:

> I have reviewed what you have implemented so far. While the database and auth logic are present, the routing is broken (causing 404s), and the fundamental workflow of the app is missing (e.g., there is no way for a tech to actually view the details of a single ticket and update its status).
>
> I have created a file called `@CURSOR_MASTER_PLAN.md`.
>
> **Your Task:**
> 1. Read `@CURSOR_MASTER_PLAN.md` very carefully.
> 2. Re-organize the existing files to match the strict `/pages/`, `/actions/`, and `/includes/` structure defined in Section 2. Fix any broken links or form actions that result from this move.
> 3. Build the missing `includes/sidebar.php` logic.
> 4. Build the missing `pages/view_ticket.php` and `actions/update_ticket.php` so the technicians actually have a way to process the tickets.
>
> Do this step-by-step and tell me what you are moving or creating first.