# Database Schema & Data Dictionary — GIA

# Schéma de base de données & dictionnaire de données — GIA


**Objectif :** Décrire le **squelette** du système : tables, colonnes, clés et liens entre elles. Utilisez les **tables Markdown** ci‑dessous dans la thèse ; utilisez le bloc **dbdiagram.io** à la fin pour générer un MCD/ERD visuel.


**Base de données :** Microsoft SQL Server (par ex. `GIA_IncidentDB`).


---


## 1. Modèle Entité‑Association (conceptuel)


- Un **utilisateur** peut créer plusieurs **incidents** (`incidents.user_id` → `users.id`).
- Un **utilisateur** (technicien) peut être assigné à plusieurs **incidents** (`incidents.assigned_to` → `users.id`, nullable).
- Un **incident** possède plusieurs **lignes de journal** (`incident_logs.incident_id` → `incidents.id`).
- Un **incident** peut avoir plusieurs **pièces jointes** (`attachments.incident_id` → `incidents.id`).


---


## 2. Table : `users`


Stocke les comptes et les rôles.


| Colonne | Type de donnée | Clé | Description |
|---------|----------------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Identifiant unique de chaque utilisateur. |
| `username` | NVARCHAR(100) | UNIQUE | Identifiant de connexion / nom affiché. |
| `email` | NVARCHAR(255) | UNIQUE | Adresse e‑mail. |
| `password_hash` | NVARCHAR(255) | | Hachage stocké du mot de passe (pas en clair). |
| `role` | VARCHAR(20) | | L’un de : `Reporter`, `Technician`, `Admin`. |
| `department` | NVARCHAR(100) | | Information organisationnelle optionnelle. |
| `created_at` | DATETIME | | Date de création du compte. |


---


## 3. Table : `incidents`


Stocke chaque ticket (incident) — l’objet métier central.


| Colonne | Type de donnée | Clé | Description |
|---------|----------------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Numéro de ticket unique. |
| `user_id` | INT | **FK → users.id** | Reporter / créateur du ticket. |
| `assigned_to` | INT NULL | **FK → users.id** | Technicien assigné (NULL si non assigné). |
| `title` | NVARCHAR(255) | | Titre court de l’incident. |
| `description` | NTEXT | | Description complète. |
| `category` | NVARCHAR(50) | | Catégorie (par ex. domaine applicatif). |
| `priority` | VARCHAR(20) | | `Critical`, `Major` ou `Minor`. |
| `status` | VARCHAR(20) | | État du cycle de vie (Open, Assigned, …). |
| `created_at` | DATETIME | | Date de création du ticket. |
| `updated_at` | DATETIME NULL | | Dernière mise à jour (aussi maintenue par trigger). |
| `closed_at` | DATETIME NULL | | Date de clôture du ticket, si applicable. |


**Relations :**


- `user_id` référence **`users.id`** (créateur).
- `assigned_to` référence **`users.id`** (assigné) ; `ON DELETE SET NULL` si la ligne utilisateur est supprimée (politique dépendant de l’usage de la BD).


---


## 4. Table : `incident_logs`


Journal d’audit de type append‑only pour les actions sur les tickets.


| Colonne | Type de donnée | Clé | Description |
|---------|----------------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Identifiant unique de ligne de journal. |
| `incident_id` | INT | **FK → incidents.id** | Ticket concerné. |
| `user_id` | INT | **FK → users.id** | Utilisateur ayant réalisé l’action. |
| `action_type` | VARCHAR(50) | | Par ex. `Creation`, `Assignment`, `Status Change`, `Comment`. |
| `message` | NVARCHAR(500) NULL | | Détail en texte libre. |
| `timestamp` | DATETIME | | Date et heure de l’événement. |


**Cascade :** `incident_id` utilise **ON DELETE CASCADE** — si un incident est supprimé, ses journaux sont supprimés (choix de politique ; insister sur les sauvegardes pour les besoins d’audit).


---


## 5. Table : `attachments`


Fichiers liés à un incident.


| Colonne | Type de donnée | Clé | Description |
|---------|----------------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Identifiant unique de pièce jointe. |
| `incident_id` | INT | **FK → incidents.id** | Ticket parent. |
| `file_path` | NVARCHAR(500) | | Chemin de stockage sur le serveur. |
| `file_name` | NVARCHAR(255) | | Nom de fichier original. |
| `uploaded_at` | DATETIME | | Date et heure de l’upload. |


---


## 6. Optionnel : trigger sur `incidents`


- **`TR_incidents_updated_at`** — Après un **UPDATE** sur `incidents`, renseigne **`updated_at`** pour les lignes modifiées.  
  Mentionnez‑le comme **mise à jour automatique du timestamp**.


---


## 7. ERD visuel — à coller dans [dbdiagram.io](https://dbdiagram.io)


Copiez le bloc ci‑dessous dans dbdiagram.io → un diagramme sera généré que vous pourrez exporter pour la thèse.

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
