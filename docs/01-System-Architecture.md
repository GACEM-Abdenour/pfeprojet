# System Architecture Overview — GIA (Plateforme GIA)

**Purpose:** Explain the *big picture* in simple terms for thesis defense: how the user interface, the application logic, and the database work together.

---

## 1. Three‑Tier Architecture (what it means)

This application follows a **3‑Tier Architecture**. Think of three layers that each have a clear job:

| Tier | Name (technical) | Plain language |
|------|------------------|----------------|
| **1** | **Presentation / Frontend** | What the user *sees* and *clicks* (screens, forms, buttons). |
| **2** | **Application / Backend** | The *rules* and *workflows* (who can do what, how a ticket is updated). |
| **3** | **Data / Database** | Where information is *stored* safely and for a long time. |

These layers are **separated** so you can change the look of the app without breaking the rules, and change the rules without losing data—as long as each layer talks to the next in a controlled way.

---

## 2. The restaurant metaphor

| Layer | Metaphor | In GIA |
|-------|-----------|--------|
| **Frontend** | **The waiter** | Takes your order, brings the menu, shows the plate. Does not cook and does not store food in bulk. |
| **Backend (PHP)** | **The kitchen** | Receives the order, applies recipes (business rules), coordinates who prepares what. |
| **Database (SQL Server)** | **The pantry / stockroom** | Holds all ingredients and finished dishes *records*: users, tickets, logs. Nothing is “lost in the kitchen” unless the rules allow it. |

- The **customer** (user) only talks to the **waiter** (browser + HTML/CSS/Bootstrap pages).
- The **waiter** sends requests to the **kitchen** (PHP scripts and `pages/*.php`).
- The **kitchen** reads and writes ingredients in the **pantry** (SQL Server via PDO).

---

## 3. What each tier uses in this project

### Frontend (the “face”)

- **HTML** — structure of pages (titles, tables, forms).
- **CSS** (including Bootstrap and custom styles) — layout, colors, responsiveness.
- **JavaScript** (e.g. DataTables, charts) — richer tables and dashboards in the browser.

The browser **displays** data; it does **not** hold the official copy of tickets or passwords. After login, it shows what the server sends.

### Backend (the “brain”)

- **PHP** runs on the **server** (e.g. with the built‑in PHP web server or IIS/Apache).
- **Key responsibilities:**
  - **Authentication:** login, session (`$_SESSION`), logout.
  - **Authorization:** `requireLogin()`, `requireRole('Admin'|'Technician'|'Reporter')` — only the right role can open certain pages or actions.
  - **Business logic:** creating a ticket, assigning a technician, changing status, writing to `incident_logs`.
- **Actions** live in files like `actions/update_ticket.php`, `actions/submit_ticket.php` — they process POST requests and redirect or redirect with errors.

### Database (the “memory”)

- **Microsoft SQL Server** (Express in this project) stores tables: `users`, `incidents`, `incident_logs`, `attachments`, etc.
- **PDO** (PHP Data Objects) is the **safe bridge** between PHP and SQL Server: parameterized queries help prevent SQL injection.

> **Note:** The guide you may have seen sometimes says “MySQL”; **this project is implemented with SQL Server**. The *idea* of a relational database is the same: tables, keys, relationships.

---

## 4. How the parts “talk” to each other (request flow)

Typical flow when a user **updates a ticket**:

1. User fills a form on a **page** (e.g. `pages/view_ticket.php`) and clicks Submit.
2. The browser sends an **HTTP POST** to an **action** script (e.g. `actions/update_ticket.php`).
3. PHP checks **session** and **role**, then runs **SQL** (transaction + `UPDATE` on `incidents`).
4. PHP inserts a row into **`incident_logs`** for traceability.
5. PHP sends a **redirect** back to the ticket page with success or error in the URL.

So: **Browser → PHP → Database → PHP → Browser.** The database is never exposed directly to the end user.

---

## 5. One diagram (mental model)

```
[ User / Browser ]
        |
        v
   HTML / CSS / JS  (Presentation)
        |
        v
      PHP pages & actions  (Application / business rules)
        |
        v
   PDO  ----------------->  SQL Server
                            (users, incidents, incident_logs, …)
```

---

## 6. Why this matters for the thesis

- You can say: **“We separated concerns: the interface presents information; PHP enforces workflow and security; the database guarantees persistence and auditability.”**
- You can defend **scalability** and **maintenance**: new reports or screens can reuse the same backend and database rules.

---

*Document aligned with the GIA codebase: PHP + Bootstrap + SQL Server.*
