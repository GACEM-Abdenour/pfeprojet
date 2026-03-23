# Database Schema & Data Dictionary — GIA

**Purpose:** Describe the **skeleton** of the system: tables, columns, keys, and how they link. Use the **Markdown tables** below in your thesis; use the **dbdiagram.io** block at the end to generate a visual ERD.

**Database:** Microsoft SQL Server (e.g. `GIA_IncidentDB`).

---

## 1. Entity Relationship (conceptual)

- One **user** can create many **incidents** (`incidents.user_id` → `users.id`).
- One **user** (technician) can be assigned to many **incidents** (`incidents.assigned_to` → `users.id`, nullable).
- One **incident** has many **log lines** (`incident_logs.incident_id` → `incidents.id`).
- One **incident** can have many **attachments** (`attachments.incident_id` → `incidents.id`).

---

## 2. Table: `users`

Stores accounts and roles.

| Column | Data type | Key | Description |
|--------|-----------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Unique identifier for each user. |
| `username` | NVARCHAR(100) | UNIQUE | Login / display name. |
| `email` | NVARCHAR(255) | UNIQUE | Email address. |
| `password_hash` | NVARCHAR(255) | | Stored hash of the password (not plain text). |
| `role` | VARCHAR(20) | | One of: `Reporter`, `Technician`, `Admin`. |
| `department` | NVARCHAR(100) | | Optional organizational info. |
| `created_at` | DATETIME | | When the account was created. |

---

## 3. Table: `incidents`

Stores each ticket (incident) — the core business object.

| Column | Data type | Key | Description |
|--------|-----------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Unique ticket number. |
| `user_id` | INT | **FK → users.id** | Reporter / creator of the ticket. |
| `assigned_to` | INT NULL | **FK → users.id** | Technician assigned (NULL if unassigned). |
| `title` | NVARCHAR(255) | | Short title of the incident. |
| `description` | NTEXT | | Full description. |
| `category` | NVARCHAR(50) | | Category (e.g. application area). |
| `priority` | VARCHAR(20) | | `Critical`, `Major`, or `Minor`. |
| `status` | VARCHAR(20) | | Lifecycle state (Open, Assigned, …). |
| `created_at` | DATETIME | | When the ticket was created. |
| `updated_at` | DATETIME NULL | | Last update (also maintained by trigger). |
| `closed_at` | DATETIME NULL | | When the ticket was closed, if applicable. |

**Relationships:**

- `user_id` references **`users.id`** (creator).
- `assigned_to` references **`users.id`** (assignee); `ON DELETE SET NULL` if the user row were deleted (policy depends on DB usage).

---

## 4. Table: `incident_logs`

Append‑only audit trail for actions on tickets.

| Column | Data type | Key | Description |
|--------|-----------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Unique log line id. |
| `incident_id` | INT | **FK → incidents.id** | Which ticket. |
| `user_id` | INT | **FK → users.id** | Who performed the action. |
| `action_type` | VARCHAR(50) | | e.g. `Creation`, `Assignment`, `Status Change`, `Comment`. |
| `message` | NVARCHAR(500) NULL | | Free‑text detail. |
| `timestamp` | DATETIME | | When the event occurred. |

**Cascade:** `incident_id` uses **ON DELETE CASCADE** — if an incident is deleted, its logs are removed (policy choice; emphasize backups for audit needs).

---

## 5. Table: `attachments`

Files linked to an incident.

| Column | Data type | Key | Description |
|--------|-----------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Unique attachment id. |
| `incident_id` | INT | **FK → incidents.id** | Parent ticket. |
| `file_path` | NVARCHAR(500) | | Storage path on the server. |
| `file_name` | NVARCHAR(255) | | Original file name. |
| `uploaded_at` | DATETIME | | Upload time. |

---

## 6. Optional: trigger on `incidents`

- **`TR_incidents_updated_at`** — After an **UPDATE** on `incidents`, sets **`updated_at`** for the changed rows.  
  Mention this as **automatic timestamp maintenance**.

---

## 7. Visual ERD — paste into [dbdiagram.io](https://dbdiagram.io)

Copy the block below into dbdiagram.io → it will render a diagram you can export for the thesis.

```dbml
// GIA - simplified ERD for dbdiagram.io
// https://dbdiagram.io

Table users {
  id int [pk, increment]
  username nvarchar(100) [not null, unique]
  email nvarchar(255) [not null, unique]
  password_hash nvarchar(255) [not null]
  role varchar(20) [not null, note: 'Reporter | Technician | Admin']
  department nvarchar(100)
  created_at datetime [not null]
}

Table incidents {
  id int [pk, increment]
  user_id int [not null, ref: > users.id]
  assigned_to int [ref: > users.id, note: 'nullable FK']
  title nvarchar(255) [not null]
  description ntext [not null]
  category nvarchar(50) [not null]
  priority varchar(20) [not null]
  status varchar(20) [not null, default: 'Open']
  created_at datetime [not null]
  updated_at datetime
  closed_at datetime
}

Table incident_logs {
  id int [pk, increment]
  incident_id int [not null, ref: > incidents.id]
  user_id int [not null, ref: > users.id]
  action_type varchar(50) [not null]
  message nvarchar(500)
  timestamp datetime [not null]
}

Table attachments {
  id int [pk, increment]
  incident_id int [not null, ref: > incidents.id]
  file_path nvarchar(500) [not null]
  file_name nvarchar(255) [not null]
  uploaded_at datetime [not null]
}
```

> **Tip:** In dbdiagram.io you can tweak colors and export PNG/PDF for slides.

---

*Derived from `database/schema.sql`.*
