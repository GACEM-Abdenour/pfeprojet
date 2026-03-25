# Test Data Documentation - GIA Incident Management Platform

**Generated:** February 12, 2026  
**Purpose:** Complete reference of all test users, incidents, and data for testing the application

---

## 🔐 Test User Credentials

> **Important (for current test data):**  
> All test users share the SAME password: **`password`**.  
> This matches the hash currently inserted by `test/insert_test_data.sql`.  
> You can change this later using PHP `password_hash()`.

### Admin Users

| Username | Password | Email | Role | Department |
|----------|----------|-------|------|------------|
| **admin** | `password` | admin@naftal.dz | Admin | Groupe Informatique |

### Technician Users

| Username | Password | Email | Role | Department |
|----------|----------|-------|------|------------|
| **tech1** | `password` | tech1@naftal.dz | Technician | Support IT |
| **tech2** | `password` | tech2@naftal.dz | Technician | Support IT |

### Reporter Users (Employees)

| Username | Password | Email | Role | Department |
|----------|----------|-------|------|------------|
| **reporter1** | `password` | reporter1@naftal.dz | Reporter | Branche Carburants |
| **reporter2** | `password` | reporter2@naftal.dz | Reporter | Branche Carburants |

---

## 📋 Test Incidents

### Incident #1: Imprimante HP ne fonctionne pas
- **Reporter:** reporter1
- **Assigned To:** tech1
- **Category:** Hardware
- **Priority:** Major
- **Status:** Diagnostic
- **Description:** L'imprimante HP LaserJet dans le bureau 205 ne répond plus. Le voyant d'erreur clignote en rouge. Déjà essayé de redémarrer l'imprimante mais le problème persiste.
- **Created:** 2 days ago
- **Updated:** 1 day ago
- **Attachments:** screenshot_erreur_imprimante.png
- **Logs:** 3 entries (Assignment, Status Change, Comment)

### Incident #2: Accès refusé au système de gestion
- **Reporter:** reporter2
- **Assigned To:** *(Unassigned)*
- **Category:** Access
- **Priority:** Critical
- **Status:** Open
- **Description:** Je ne peux plus accéder au système de gestion des stocks. Message d'erreur: "Accès refusé - Contactez l'administrateur". Mon compte fonctionnait hier.
- **Created:** 1 day ago
- **Attachments:** error_log_access_denied.txt
- **Logs:** None yet

### Incident #3: Réseau WiFi lent dans le bâtiment A
- **Reporter:** reporter1
- **Assigned To:** tech2
- **Category:** Network
- **Priority:** Major
- **Status:** Assigned
- **Description:** La connexion WiFi est très lente depuis ce matin dans le bâtiment A. Les pages web mettent beaucoup de temps à charger. Problème intermittent.
- **Created:** 5 hours ago
- **Updated:** 4 hours ago
- **Attachments:** network_speed_test.png
- **Logs:** 1 entry (Assignment)

### Incident #4: Erreur dans l'application Excel
- **Reporter:** reporter2
- **Assigned To:** tech1
- **Category:** Software
- **Priority:** Minor
- **Status:** Resolved
- **Description:** L'application Excel plante régulièrement lors de l'ouverture de fichiers volumineux. Message d'erreur: "Excel a rencontré un problème et doit fermer".
- **Created:** 3 days ago
- **Updated:** 1 day ago
- **Closed:** 1 day ago
- **Logs:** 2 entries (Status Change to Resolved, Status Change to Closed)

### Incident #5: Écran d'ordinateur noir
- **Reporter:** reporter1
- **Assigned To:** *(Unassigned)*
- **Category:** Hardware
- **Priority:** Critical
- **Status:** Open
- **Description:** L'écran de mon ordinateur reste noir au démarrage. L'ordinateur semble démarrer (voyants allumés) mais l'écran ne s'allume pas.
- **Created:** 2 hours ago
- **Logs:** None yet

### Incident #6: Problème d'impression PDF
- **Reporter:** reporter2
- **Assigned To:** tech2
- **Category:** Software
- **Priority:** Minor
- **Status:** Diagnostic
- **Description:** Impossible d'imprimer des fichiers PDF depuis Adobe Reader. L'option d'impression est grisée. Fonctionne avec d'autres types de fichiers.
- **Created:** 1 day ago
- **Updated:** 12 hours ago
- **Logs:** None yet

### Incident #7: Serveur de fichiers inaccessible
- **Reporter:** reporter1
- **Assigned To:** tech1
- **Category:** Network
- **Priority:** Critical
- **Status:** Failed/Blocked
- **Description:** Le serveur de fichiers partagés (\\fileserver\departement) n'est plus accessible depuis ce matin. Erreur: "Le chemin réseau est introuvable".
- **Created:** 1 week ago
- **Updated:** 5 days ago
- **Closed:** 5 days ago
- **Logs:** 1 entry (Status Change to Failed/Blocked)

### Incident #8: Mise à jour Windows nécessaire
- **Reporter:** reporter2
- **Assigned To:** tech1
- **Category:** Software
- **Priority:** Minor
- **Status:** Closed
- **Description:** Mon ordinateur demande constamment de redémarrer pour installer des mises à jour Windows, mais je ne peux pas le faire maintenant car j'ai un travail urgent.
- **Created:** 1 week ago
- **Updated:** 6 days ago
- **Closed:** 6 days ago
- **Logs:** None

---

## 📎 Test Attachments

| Incident ID | File Name | File Path |
|-------------|-----------|-----------|
| #1 | screenshot_erreur_imprimante.png | uploads/incident_1_screenshot1.png |
| #2 | error_log_access_denied.txt | uploads/incident_2_error_log.txt |
| #3 | network_speed_test.png | uploads/incident_3_network_test.png |

**Note:** These are placeholder file paths. Actual files should be uploaded through the application interface.

---

## 📝 Test Incident Logs

### Incident #1 Logs
1. **Assignment** (tech1, 2 days ago)
   - Message: "Ticket assigné à tech1"

2. **Status Change** (tech1, 1 day ago)
   - Message: "Statut changé de 'Assigned' à 'Diagnostic'"

3. **Comment** (tech1, 12 hours ago)
   - Message: "Vérifié les câbles et redémarré l'imprimante. Le problème persiste. En attente de pièce de rechange."

### Incident #2 Logs
- None yet (newly created, unassigned)

### Incident #3 Logs
1. **Assignment** (tech2, 4 hours ago)
   - Message: "Ticket assigné à tech2"

### Incident #4 Logs
1. **Status Change** (tech1, 1 day ago)
   - Message: "Statut changé de 'Diagnostic' à 'Resolved'"

2. **Status Change** (reporter2, 1 day ago)
   - Message: "Statut changé de 'Resolved' à 'Closed' - Problème résolu"

### Incident #7 Logs
1. **Status Change** (tech1, 5 days ago)
   - Message: "Statut changé à 'Failed/Blocked' - Problème nécessite intervention externe"

---

## 🎯 Testing Scenarios

### Scenario 1: Admin Login
1. Login as `admin` / `password`
2. Should redirect to `admin_dashboard.php`
3. Can view all incidents and statistics

### Scenario 2: Technician Login
1. Login as `tech1` / `password`
2. Should redirect to `tech_dashboard.php`
3. Can see assigned incidents (#1, #4, #7)
4. Can see unassigned incidents (#2, #5)
5. Can take tickets and update status

### Scenario 3: Reporter Login
1. Login as `reporter1` / `password`
2. Should redirect to `create_ticket.php`
3. Can create new incidents
4. Can view own incidents (#1, #3, #5, #7)

### Scenario 4: Workflow Testing
1. Reporter creates incident → Status: "Open"
2. Technician takes ticket → Status: "Assigned"
3. Technician investigates → Status: "Diagnostic"
4. Technician resolves → Status: "Resolved"
5. Reporter confirms → Status: "Closed"

### Scenario 5: Priority Testing
- **Critical:** Incidents #2, #5, #7 (should appear first)
- **Major:** Incidents #1, #3
- **Minor:** Incidents #4, #6, #8

### Scenario 6: Status Filtering
- **Open:** #2, #5
- **Assigned:** #3
- **Diagnostic:** #1, #6
- **Resolved:** #4
- **Closed:** #8
- **Failed/Blocked:** #7

---

## 📊 Data Statistics

- **Total Users:** 6
  - Admins: 2
  - Technicians: 2
  - Reporters: 2

- **Total Incidents:** 8
  - Open: 2
  - Assigned: 1
  - Diagnostic: 2
  - Resolved: 1
  - Closed: 1
  - Failed/Blocked: 1

- **Total Attachments:** 3
- **Total Log Entries:** 7

---

## 🔄 How to Reset Test Data

To clear and re-insert test data:

```sql
-- Clear existing data (in order due to foreign keys)
DELETE FROM incident_logs;
DELETE FROM attachments;
DELETE FROM incidents;
DELETE FROM users;

-- Then run: php test/insert_test_data.php
```

Or use the PowerShell script:
```powershell
.\setup_database.ps1  # This will drop and recreate all tables
php test/insert_test_data.php  # Then insert fresh test data
```

---

## 📝 Notes

- All passwords are hashed using `password_hash()` with `PASSWORD_DEFAULT`
- Dates are relative (e.g., "2 days ago") - actual timestamps are calculated dynamically
- File attachments are placeholder paths - actual files should be uploaded
- All text is in French to match client requirements
- Incident IDs are auto-generated (IDENTITY), so actual IDs may vary

---

**Last Updated:** February 12, 2026
