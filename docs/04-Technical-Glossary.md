# Glossaire technique — Aide‑mémoire pour non‑informaticiens (GIA)


Courtes définitions que vous pouvez utiliser **telles quelles** ou reformuler pendant la soutenance.


---


## Application & données


| Terme | Explication simple |
|-------|---------------------|
| **CRUD** | **C**reate, **R**ead, **U**pdate, **D**elete — les quatre opérations de base qu’une application effectue sur les données. GIA crée des tickets, les lit, met à jour les statuts et peut les supprimer uniquement là où la conception l’autorise. |
| **Session** | Après la connexion, le **serveur** se souvient de qui vous êtes pendant un certain temps grâce à une **session** (PHP `$_SESSION`). Comme un bracelet à un événement : le site vous reconnaît sans redemander le mot de passe à chaque clic. |
| **Cookie** | Petit morceau de donnée stocké par le navigateur ; peut contenir un **identifiant de session** pour que le serveur reconnaisse votre session. |
| **PDO** | **PHP Data Objects** — une **façon standard et sécurisée** pour PHP de se connecter à SQL Server (ou d’autres bases) et d’exécuter des requêtes, en particulier avec des **paramètres** (placeholders) pour limiter les risques d’injection SQL. |
| **SQL** | Langage pour **interroger** les bases de données relationnelles (`SELECT`, `INSERT`, `UPDATE`). |
| **SQL Server** | SGBD relationnel de Microsoft utilisé dans ce projet pour stocker utilisateurs, tickets et journaux. |
| **Primary Key (PK)** | Colonne (ou ensemble de colonnes) qui **identifie de manière unique** une ligne dans une table (par ex. `users.id`). |
| **Foreign Key (FK)** | Colonne qui **pointe vers** la clé primaire d’une autre table (par ex. `incidents.user_id` → `users.id`), ce qui impose des **relations**. |
| **Transaction** | **Groupe d’opérations** sur la base qui **réussissent toutes** ou sont **toutes annulées** — utilisé pour que la mise à jour d’un ticket et sa ligne de journal restent cohérentes. |


---


## Web & sécurité


| Terme | Explication simple |
|-------|---------------------|
| **HTTP** | Protocole que le navigateur utilise pour **demander** des pages et **envoyer** des formulaires au serveur. |
| **POST** | Type de requête utilisé pour **soumettre des données** (par ex. formulaires), souvent pour des actions qui modifient quelque chose sur le serveur. |
| **Redirect** | Après une action, le serveur dit au navigateur **« va à cette URL »** (par ex. retour à la page du ticket avec `?success=` ou `?error=`). |
| **Middleware** (concept) | Code qui s’exécute **avant** la logique principale de la page. Dans GIA, **`requireLogin()`** et **`requireRole(...)`** jouent le rôle de **vigile** : si vous n’êtes pas autorisé, vous êtes renvoyé vers la connexion ou une erreur — **avant** l’exécution de code sensible. |
| **SQL injection** | Attaque où une saisie malveillante est interprétée comme du SQL. Les **requêtes paramétrées** (placeholders PDO) réduisent ce risque. |
| **Password hash** | Stocker une chaîne **dérivée** du mot de passe, et non le mot de passe lui‑même, pour qu’une fuite de base ne révèle pas facilement les mots de passe bruts. |


---


## Frontend


| Terme | Explication simple |
|-------|---------------------|
| **HTML** | Structure des pages web (titres, tableaux, formulaires). |
| **CSS** | Mise en forme (couleurs, espacements, polices). |
| **Bootstrap** | **Framework CSS** avec des composants prêts à l’emploi (boutons, grilles, tableaux) pour une interface cohérente. |
| **JavaScript** | S’exécute dans le navigateur pour des comportements **interactifs** (par ex. tableaux triables, graphiques). |


---


## Phrases prêtes pour la soutenance


- **« Nous utilisons une architecture en trois couches : présentation dans le navigateur, règles métier en PHP, persistance dans SQL Server. »**
- **« La ligne d’incident contient l’état courant ; `incident_logs` contient l’historique pour l’audit. »**
- **« Les sessions identifient l’utilisateur après connexion ; `requireRole` applique les permissions par page. »**


---


*Adapté au projet GIA / Plateforme GIA.*
