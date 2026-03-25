# Architecture & documentation — soutenance de thèse (Plateforme GIA)


Ce document est rédigé pour des **non-informaticiens** : il explique **comment la plateforme fonctionne** sans entrer dans le détail du code source.


---


## 1. L’architecture globale (3-Tier)


L’application suit une **architecture à trois niveaux** : on sépare ce que l’utilisateur **voit**, ce que le serveur **décide**, et où les données sont **stockées** de façon durable.


| Niveau | Rôle technique | Rôle métier |
|--------|----------------|-------------|
| **Présentation (Frontend)** | Pages web : HTML, **Bootstrap**, CSS, un peu de JavaScript (tableaux, graphiques). | C’est l’**interface** : formulaires, tableaux de bord, boutons. |
| **Logique métier (Backend)** | **PHP** sur le serveur : authentification, règles « qui peut faire quoi », mise à jour des tickets. | C’est le **cerveau** : appliquer les règles de l’organisation. |
| **Données (Base de données)** | **Microsoft SQL Server** : tables `users`, `incidents`, `incident_logs`, etc. | C’est la **mémoire** fiable et structurée. |


### Métaphore du restaurant


| Élément | Analogie | Dans GIA |
|---------|----------|----------|
| **Le client** | Personne qui commande. | L’utilisateur connecté (déclarant, technicien, admin). |
| **Le serveur / la salle** | Prend la commande, présente le plat. | Le **Frontend** : navigateur, écrans, formulaires (**Bootstrap**). |
| **La cuisine** | Prépare le plat selon la recette et les règles d’hygiène. | Le **Backend** (**PHP**) : validation, rôles, enregistrement des actions. |
| **La réserve / le garde-manger** | Stocke les ingrédients de façon organisée. | La **base SQL Server** : comptes, tickets, journal d’audit. |


Le client ne va pas **directement** dans la réserve : tout passe par la **cuisine** (PHP), ce qui permet de **contrôler** ce qui est lu ou modifié.


---


## 2. L’environnement de déploiement

> **Guide pas à pas (IIS, PHP, SQL Server, droits, checklist)** : voir le document dédié  
> **[05-Deploiement-Windows-Server-2019.md](./05-Deploiement-Windows-Server-2019.md)** — c’est la version **opérationnelle** détaillée du même sujet que dans le mémoire (chapitre Réalisation).

### Pourquoi un serveur **Windows Server 2019** (ex. contexte **Standard Naftal**) plutôt qu’un simple PC Windows 11 ?


| Critère | PC local (Windows 11) | Serveur dédié (Windows Server 2019, en entreprise) |
|---------|------------------------|-----------------------------------------------------|
| **Disponibilité** | Souvent éteint ou en veille ; dépend d’une seule machine. | Conçu pour tourner **24h/24, 7j/7** : l’application reste accessible aux équipes. |
| **Sécurité & périmètre** | Réseau domestique ou poste nomade ; exposition Internet plus risquée si mal configurée. | Déploiement **sur site (on-premise)** : les données peuvent rester dans le **réseau de l’organisation**, avec pare-feu, sauvegardes et politiques centralisées. |
| **Rôle métier** | Outil personnel ou démo. | **Service mutualisé** : plusieurs utilisateurs (déclarants, techniciens, administration) accèdent au même système de façon contrôlée. |
| **Exploitation** | Peu adapté à une charge et une supervision « production ». | Adapté aux **standards** d’entreprise (comptes de service, journalisation, redondance possible). |


En résumé : le **PC Windows 11** convient au **développement et aux tests** ; le **serveur Windows Server** correspond à un **service de production** fiable, sécurisé et aligné sur les exigences d’une structure comme **Naftal** (disponibilité, contrôle des données, exploitation centralisée).


---


## 3. Le cycle de vie d’un incident (ticket)


Un **incident** est un enregistrement dans la table `incidents`. Son **statut** indique où il se trouve dans le processus. En pratique, la chaîne ressemble à ceci :


- **Ouvert (`Open`)** — Le ticket est **créé** par un déclarant ; **personne n’est encore responsable** côté support (ou il a été **remis en file**).
- **Assigné (`Assigned`)** — Un **technicien** est **lié** au ticket (`assigned_to`) : quelqu’un en a la charge.
- **En diagnostic (`Diagnostic`)** — Le travail est **en cours** : analyse, tests, correction en cours.
- **Résolu (`Resolved`)** — Le problème est **traité** du point de vue technique ; une clôture peut suivre.
- **Clos (`Closed`)** — Le dossier est **fermé** administrativement (souvent avec une date de clôture).
- **Échec / bloqué (`Failed/Blocked`)** — Issue **finale** lorsque l’incident ne peut pas être résolu dans les conditions prévues (blocage externe, impossibilité technique, etc.).


Des transitions supplémentaires (retour en file non assignée, etc.) sont gérées par la **logique applicative** (PHP), toujours dans le respect des rôles (déclarant, technicien, administrateur).


### Traçabilité : la table `incident_logs`


Chaque action importante peut être enregistrée dans **`incident_logs`** (création, assignation, changement de statut, commentaire, etc.) avec :


- **quel ticket** (`incident_id`),
- **quel utilisateur** (`user_id`),
- **quel type d’action** (`action_type`),
- **un message** descriptif (`message`),
- **quand** (`timestamp`).


Cela garantit une **traçabilité complète** : on peut reconstituer **l’historique** pour l’audit, la sécurité et la confiance entre services — **sans s’appuyer uniquement** sur l’écran courant du ticket.


---


## 4. Schéma de la base de données (tables principales)


Tableaux à copier dans un outil type **dbdiagram.io**, **Excel** ou le mémoire de thèse. Les clés **PK** = primaire, **FK** = étrangère.


### Table `users` (utilisateurs)


| Colonne | Type (schéma) | Clé | Description |
|---------|----------------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Identifiant unique de la personne. |
| `username` | NVARCHAR(100) | UNIQUE | Nom de connexion. |
| `email` | NVARCHAR(255) | UNIQUE | Adresse e-mail. |
| `password_hash` | NVARCHAR(255) | | Mot de passe stocké sous forme de **hash** (pas en clair). |
| `role` | VARCHAR(20) | | Rôle : **Reporter**, **Technician**, **Admin**. |
| `department` | NVARCHAR(100) | | Service / département (optionnel). |
| `created_at` | DATETIME | | Date de création du compte. |


### Table `incidents` (tickets / incidents)


| Colonne | Type (schéma) | Clé | Description |
|---------|----------------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Numéro unique du ticket. |
| `user_id` | INT | **FK → users.id** | Déclarant / créateur du ticket. |
| `assigned_to` | INT NULL | **FK → users.id** | Technicien assigné (NULL si personne). |
| `title` | NVARCHAR(255) | | Titre court. |
| `description` | NTEXT | | Description détaillée. |
| `category` | NVARCHAR(50) | | Catégorie métier. |
| `priority` | VARCHAR(20) | | Priorité (ex. Critical, Major, Minor). |
| `status` | VARCHAR(20) | | Statut du cycle de vie (Open, Assigned, …). |
| `created_at` | DATETIME | | Date de création. |
| `updated_at` | DATETIME NULL | | Dernière mise à jour. |
| `closed_at` | DATETIME NULL | | Date de clôture si applicable. |


### Table `incident_logs` (journal d’audit)


| Colonne | Type (schéma) | Clé | Description |
|---------|----------------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Identifiant de la ligne de journal. |
| `incident_id` | INT | **FK → incidents.id** | Ticket concerné. |
| `user_id` | INT | **FK → users.id** | Auteur de l’action. |
| `action_type` | VARCHAR(50) | | Type d’événement (ex. Creation, Assignment, Status Change). |
| `message` | NVARCHAR(500) NULL | | Détail lisible de l’action. |
| `timestamp` | DATETIME | | Date et heure de l’événement. |


### Table `attachments` (pièces jointes) — vue d’ensemble


| Colonne | Type (schéma) | Clé | Description |
|---------|----------------|-----|-------------|
| `id` | INT IDENTITY | **PK** | Identifiant du fichier. |
| `incident_id` | INT | **FK → incidents.id** | Ticket parent. |
| `file_path` | NVARCHAR(500) | | Chemin de stockage sur le serveur. |
| `file_name` | NVARCHAR(255) | | Nom d’affichage du fichier. |
| `uploaded_at` | DATETIME | | Date d’envoi. |


---


## 5. Glossaire technique (rappel rapide)


| Terme | Définition simple |
|-------|-------------------|
| **CRUD** | **C**reate, **R**ead, **U**pdate, **D**elete — les quatre opérations de base sur des données : créer, lire, modifier, supprimer. |
| **Session** | Mécanisme par lequel le **serveur** « se souvient » qu’un utilisateur est **connecté** après la saisie du mot de passe (sans redemander le mot de passe à chaque clic). |
| **Serveur web** | Logiciel (souvent sur Windows Server) qui **reçoit les requêtes** du navigateur et **exécute** PHP, puis renvoie les pages HTML. |
| **Base de données relationnelle** | Système qui stocke l’information dans des **tables** reliées par des **clés** (ex. un ticket « pointe » vers un utilisateur). **SQL Server** en est un exemple. |


---


*Document rédigé pour la plateforme **GIA** (Gestion des Incidents Applicatifs). Pour un schéma graphique (ERD), voir aussi `docs/03-Database-Schema-and-ERD.md` et l’export dbdiagram.io.*
