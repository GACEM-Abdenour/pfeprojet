# Technical Glossary — Cheat Sheet for Non‑CS Students (GIA)

Short definitions you can use **as‑is** or paraphrase during the defense.

---

## Application & data

| Term | Simple explanation |
|------|---------------------|
| **CRUD** | **C**reate, **R**ead, **U**pdate, **D**elete — the four basic things an app does with data. GIA creates tickets, reads them, updates status, and may delete only where the design allows. |
| **Session** | After login, the **server** remembers who you are for a while using a **session** (PHP `$_SESSION`). Like a wristband at an event: the site knows you without asking password on every click. |
| **Cookie** | Small piece of data the browser stores; can hold a **session id** so the server recognizes your session. |
| **PDO** | **PHP Data Objects** — a **safe, standard way** for PHP to connect to SQL Server (or other databases) and run queries, especially with **parameters** (placeholders) to limit SQL injection risk. |
| **SQL** | Language for **querying** relational databases (`SELECT`, `INSERT`, `UPDATE`). |
| **SQL Server** | Microsoft’s **relational database** product used in this project to store users, tickets, and logs. |
| **Primary Key (PK)** | The column (or columns) that **uniquely identifies** one row in a table (e.g. `users.id`). |
| **Foreign Key (FK)** | A column that **points to** another table’s primary key (e.g. `incidents.user_id` → `users.id`), enforcing **relationships**. |
| **Transaction** | A **bundle of database operations** that either **all succeed** or **all roll back** — used so a ticket update and its log line stay consistent. |

---

## Web & security

| Term | Simple explanation |
|------|---------------------|
| **HTTP** | Protocol for the browser to **request** pages and **send** forms to the server. |
| **POST** | A type of request used to **submit data** (e.g. forms), often for actions that change something on the server. |
| **Redirect** | After an action, the server tells the browser **“go to this URL”** (e.g. back to the ticket with `?success=` or `?error=`). |
| **Middleware** (concept) | Code that runs **before** the main page logic. In GIA, **`requireLogin()`** and **`requireRole(...)`** act like a **security guard**: if you’re not allowed, you’re sent to login or an error — **before** sensitive code runs. |
| **SQL injection** | An attack where malicious input is interpreted as SQL. **Parameterized queries** (PDO placeholders) reduce this risk. |
| **Password hash** | Storing a **derived** string from the password, not the password itself, so a database leak doesn’t expose raw passwords easily. |

---

## Frontend

| Term | Simple explanation |
|------|---------------------|
| **HTML** | Structure of web pages (headings, tables, forms). |
| **CSS** | Styling (colors, spacing, fonts). |
| **Bootstrap** | A **CSS framework** with ready‑made components (buttons, grids, tables) for a consistent UI. |
| **JavaScript** | Runs in the browser for **interactive** behavior (e.g. sortable tables, charts). |

---

## Thesis‑friendly one‑liners

- **“We use a three‑tier architecture: presentation in the browser, business rules in PHP, persistence in SQL Server.”**
- **“The incident row holds the current state; incident_logs holds the history for auditing.”**
- **“Sessions identify the user after login; requireRole enforces permissions per page.”**

---

*Tailored to the GIA / Plateforme GIA project.*
