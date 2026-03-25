# Documentation du projet — Plateforme GIA

Bienvenue. Ce dossier **`docs/`** contient **toute la documentation pédagogique** pour comprendre, reprendre et présenter le projet **GIA** (Gestion des Incidents Applicatifs), en **français** et en termes accessibles aux **débutants**.

Elle est **alignée** sur le mémoire LaTeX du dossier **`memoire-pfe/`** : ce que vous devez maîtriser pour les enseignants est dans le mémoire ; ce que vous devez savoir pour **installer, faire tourner et expliquer** le système est ici.

---

## Par où commencer ?

1. Lire **`00-LIRE-EN-PREMIER.md`** (orientation et ordre de lecture).
2. Enchaîner avec **`07-Contexte-metier-et-alignement-memoire.md`** (lien mémoire ↔ code).
3. Si vous déployez sur un serveur : **`05-Deploiement-Windows-Server-2019.md`** (IIS, PHP, SQL Server — **indispensable** pour correspondre au chapitre Réalisation du mémoire).

---

## Table des matières des documents

| Fichier | Contenu |
|---------|---------|
| **[00-LIRE-EN-PREMIER.md](./00-LIRE-EN-PREMIER.md)** | Point d’entrée : ordre de lecture conseillé. |
| **[07-Contexte-metier-et-alignement-memoire.md](./07-Contexte-metier-et-alignement-memoire.md)** | Naftal, DCSI, tableau **chapitre mémoire ↔ doc**. |
| **[THESIS_ARCHITECTURE.md](./THESIS_ARCHITECTURE.md)** | **Synthèse unique** type soutenance : 3-tiers, pourquoi Windows Server, cycle de vie, schéma BDD, glossaire court. |
| **[01-System-Architecture.md](./01-System-Architecture.md)** | Architecture 3 tiers détaillée, métaphore du restaurant, flux PHP. |
| **[02-Ticket-Lifecycle.md](./02-Ticket-Lifecycle.md)** | Statuts, rôles, journal **`incident_logs`**, FAQ oral. |
| **[08-Securite-et-regles-metier.md](./08-Securite-et-regles-metier.md)** | Sécurité (session, PDO, hash) et **règles R1–R7** en langage simple. |
| **[03-Database-Schema-and-ERD.md](./03-Database-Schema-and-ERD.md)** | Dictionnaire des tables + bloc **DBML** pour [dbdiagram.io](https://dbdiagram.io). |
| **[06-Structure-du-projet-et-roles.md](./06-Structure-du-projet-et-roles.md)** | Dossiers **`pages/`**, **`actions/`**, **`includes/`**, rôles utilisateurs. |
| **[05-Deploiement-Windows-Server-2019.md](./05-Deploiement-Windows-Server-2019.md)** | **Guide complet** : IIS, FastCGI, PHP NTS, SQL Server Express, extensions `sqlsrv`, droits **`uploads/`**, checklist. |
| **[09-Connexion-base-de-donnees.md](./09-Connexion-base-de-donnees.md)** | Chaînes de connexion PHP / SQL Server (résumé pratique). |
| **[04-Technical-Glossary.md](./04-Technical-Glossary.md)** | Glossaire (CRUD, session, PDO, PK/FK, etc.). |

---

## Fichiers utiles en dehors de `docs/`

| Emplacement | Rôle |
|-------------|------|
| **`memoire-pfe/`** | Mémoire PDF (référence académique attendue par les enseignants). |
| **`CONNECTION_STRINGS.md`** (racine) | Détail des chaînes de connexion (complément technique à **`09-Connexion-base-de-donnees.md`**). |
| **`includes/db.php`** | Configuration réelle de la connexion à la base. |

---

## Checklist avant une soutenance ou une reprise du projet

- [ ] Expliquer les **3 couches** : navigateur (pages) → **PHP** (règles) → **SQL Server** (données).
- [ ] Expliquer les **3 rôles** : Reporter, Technicien, Admin — qui fait quoi.
- [ ] Décrire le **cycle de vie** du ticket (Open → Assigned → … → Closed / Failed).
- [ ] Expliquer **`incident_logs`** : pourquoi c’est la **preuve** des actions.
- [ ] Mentionner le **déploiement cible** : **Windows Server 2019**, **IIS**, **PHP 8**, **SQL Server** (voir **`05-Deploiement-Windows-Server-2019.md`**).
- [ ] Montrer le **schéma** (ERD) : généré depuis **`03-Database-Schema-and-ERD.md`** sur dbdiagram.io.

---

## Schéma visuel (ERD)

1. Ouvrir [https://dbdiagram.io](https://dbdiagram.io).
2. Coller le bloc **DBML** depuis **`03-Database-Schema-and-ERD.md`**.
3. Exporter une image (PNG/PDF) pour le mémoire ou les slides.

---

## Note importante

L’implémentation utilise **Microsoft SQL Server** et **PHP** avec **PDO** — comme dans le mémoire. Toute mention générique de « MySQL » dans d’anciens brouillons doit être **ignorée** au profit de **SQL Server** pour rester cohérent avec le projet et le mémoire.
