# Documentation thèse — Plateforme GIA

Ce dossier regroupe **les documents essentiels** pour expliquer le projet à un jury **sans jargon inutile**, en mettant l’accent sur la **logique métier** et l’**architecture**.

---

## Contenu

| Document | Contenu |
|----------|---------|
| [01-System-Architecture.md](./01-System-Architecture.md) | Architecture **3 tiers** (Frontend / PHP / SQL Server), métaphore **restaurant**. |
| [02-Ticket-Lifecycle.md](./02-Ticket-Lifecycle.md) | **Vie d’un ticket** : statuts, rôles, rôle de **`incident_logs`**. |
| [03-Database-Schema-and-ERD.md](./03-Database-Schema-and-ERD.md) | **Dictionnaire de données** + extrait **DBML** pour [dbdiagram.io](https://dbdiagram.io). |
| [04-Technical-Glossary.md](./04-Technical-Glossary.md) | **Glossaire** (CRUD, session, PDO, middleware, etc.). |

---

## Checklist avant la soutenance

- [ ] **Vue d’ensemble** : expliquer les 3 couches (interface → PHP → base).
- [ ] **Rôles utilisateurs** : **Admin** vs **Technicien** vs **Déclarant (Reporter)** — qui fait quoi.
- [ ] **Cycle de vie du ticket** : de la création à **Résolu / Clos / Échec**, avec **Assigned** et **Diagnostic** entre les deux.
- [ ] **Schéma / ERD** : table `users` ↔ `incidents` ↔ `incident_logs` (et pièces jointes si vous les présentez).
- [ ] **Journal d’actions** : **`incident_logs`** = traçabilité, pas de « disparition » silencieuse de l’historique dans le flux normal.
- [ ] **Sécurité** : session, contrôle d’accès (`requireLogin` / `requireRole`), mots de passe en **hash**, PDO paramétré.

---

## Visualiser le schéma (ERD)

1. Ouvrir [https://dbdiagram.io](https://dbdiagram.io).
2. Coller le bloc **DBML** depuis [03-Database-Schema-and-ERD.md](./03-Database-Schema-and-ERD.md).
3. Exporter une image (PNG/PDF) pour le mémoire ou les slides.

---

## Note technique

Le projet utilise **PHP** côté serveur et **Microsoft SQL Server** pour la base (voir `includes/db.php` et `database/schema.sql`). Certaines références génériques parlent de « MySQL » ; pour votre mémoire, préférez **SQL Server** pour coller à l’implémentation réelle.
