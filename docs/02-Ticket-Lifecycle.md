# Ticket Lifecycle Documentation — GIA

**Purpose:** Describe the **business logic** of a ticket from creation to closure, in terms non‑CS readers can follow. This is the core story for a thesis defense.

---

## 1. Actors (who does what?)

| Role | Typical actions |
|------|------------------|
| **Reporter** | Creates tickets (declares an incident), views their own history. |
| **Technician** | Takes unassigned tickets, works on them, updates status and comments. |
| **Admin** | Global view, can assign or unassign tickets to technicians, oversight. |

Permissions are enforced in PHP (`requireRole`, `requireLogin`) and reflected in which dashboards and buttons each user sees.

---

## 2. Statuses (what state is the ticket in?)

The `incidents.status` field uses a fixed list (see database constraints). Here is the **meaning** in business terms:

| Status | Meaning |
|--------|---------|
| **Open** | The ticket **exists** but **no technician is responsible** yet (or it was put back in the pool). |
| **Assigned** | A technician is **linked** to the ticket (`assigned_to` points to a user). Often set when someone takes the ticket or an admin assigns it. |
| **Diagnostic** | Work is **in progress** (investigation, analysis, fixes in progress). |
| **Resolved** | The problem is **fixed** from IT’s point of view; may wait for confirmation or closure. |
| **Closed** | The ticket is **finished** administratively (often with `closed_at` set). |
| **Failed / Blocked** | The ticket **cannot** be completed as planned (blocked externally, impossible fix, etc.) — still a **final outcome**, not “open”. |

> Exact spelling in the database for the last one: `Failed/Blocked` (with a slash).

---

## 3. Typical life of a ticket (happy path)

1. **Birth — Creation**  
   - A **Reporter** submits a form (`create_ticket` → `submit_ticket` / similar).  
   - A new row appears in **`incidents`** (usually `status = 'Open'`, `assigned_to` empty).  
   - An entry is written to **`incident_logs`** (e.g. action type **Creation**).

2. **Assignment**  
   - Either a **Technician** “takes” the ticket (`take_ticket`), or an **Admin** assigns someone (`assign_tech`).  
   - `assigned_to` is set; status often becomes **Assigned** if it was **Open**.  
   - **`incident_logs`** records an **Assignment** action (who did what).

3. **Work in progress**  
   - The technician can set status to **Diagnostic** while investigating.  
   - **Status changes** and **comments** are logged (`Status Change`, `Comment`).

4. **Outcome**  
   - **Resolved** or **Closed** when the work is done, or **Failed/Blocked** if it cannot be completed.  
   - For some transitions, the system requires a **comment** (business rule for traceability).  
   - **Closing** may set **`closed_at`** (depending on workflow in code).

5. **Optional: return to pool**  
   - In some flows a technician can move status back toward **Open** and clear assignment so the ticket returns to the **unassigned** list (rules are in `update_ticket.php`).

Throughout this path, **the ticket row in `incidents` is the current snapshot**; **`incident_logs` is the history**.

---

## 4. How `incident_logs` supports security and auditing

The table **`incident_logs`** is an **append‑only style trace** (new rows are inserted; the application does not “rewrite history” for normal operations).

| What it stores | Why it matters |
|----------------|----------------|
| **Which ticket** (`incident_id`) | Links the event to one incident. |
| **Who did it** (`user_id`) | Accountability — ties to `users.id`. |
| **What kind of event** (`action_type`) | e.g. Creation, Assignment, Status Change, Comment. |
| **Details** (`message`) | Human‑readable explanation (optional but valuable). |
| **When** (`timestamp`) | Ordering and forensic timeline. |

**Auditing:** If someone asks *“Who closed ticket #9 and when?”*, you query `incident_logs` instead of trusting memory.

**Security:** Even if the on‑screen ticket view is misleading, the **log** can show the sequence of actions (subject to your backup and DB access policies).

**Integrity:** Foreign keys ensure logs point to real incidents and users (see schema doc).

---

## 5. Short Q&A for the defense

- **Can a Reporter change any ticket?**  
  No — only flows allowed by PHP for that role (e.g. create and view own tickets).

- **Where is the “truth” of the current state?**  
  The **`incidents`** row (status, `assigned_to`, dates).

- **Where is the story of what happened?**  
  **`incident_logs`**.

---

*Aligned with `actions/update_ticket.php`, `actions/submit_ticket.php`, and `includes/functions.php` (`logIncidentAction`).*
