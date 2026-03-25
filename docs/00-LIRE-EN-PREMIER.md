# Lire en premier — documentation du projet GIA

Ce dossier **`docs/`** est destiné aux **étudiants**, **encadrants** et **reprises du projet** qui doivent comprendre la plateforme **sans tout savoir du code** au départ. Tout est rédigé en **français**, avec des termes simples.

## Qu’est-ce que GIA ?

**GIA** (Gestion des Incidents Applicatifs) est une application web **intranet** pour :

- **déclarer** des incidents informatiques (tickets) ;
- **les traiter** par des techniciens (file d’attente, assignation, statuts) ;
- **superviser** l’activité (tableaux de bord pour l’administrateur) ;
- **tracer** chaque action importante dans un **journal** (`incident_logs`).

C’est ce qui est décrit dans le mémoire LaTeX du dossier **`memoire-pfe/`**.

## Par où commencer ? (ordre conseillé)

| Ordre | Document | À quoi ça sert |
|------:|----------|----------------|
| 0 | **[10-Comptes-demonstration-et-Docker.md](./10-Comptes-demonstration-et-Docker.md)** | **Se connecter** (login / mots de passe) + **Docker** |
| 1 | **[00-LIRE-EN-PREMIER.md](./00-LIRE-EN-PREMIER.md)** (ce fichier) | Orientation |
| 2 | **[README.md](./README.md)** | Table des matières et checklist soutenance |
| 3 | **[07-Contexte-metier-et-alignement-memoire.md](./07-Contexte-metier-et-alignement-memoire.md)** | Lien entre le mémoire et le code |
| 4 | **[01-System-Architecture.md](./01-System-Architecture.md)** | Architecture 3 tiers, métaphore |
| 5 | **[02-Ticket-Lifecycle.md](./02-Ticket-Lifecycle.md)** | Statuts, rôles, journal d’audit |
| 6 | **[08-Securite-et-regles-metier.md](./08-Securite-et-regles-metier.md)** | Sécurité et règles R1–R7 (simple) |
| 7 | **[03-Database-Schema-and-ERD.md](./03-Database-Schema-and-ERD.md)** | Tables, clés, export ERD |
| 8 | **[06-Structure-du-projet-et-roles.md](./06-Structure-du-projet-et-roles.md)** | Dossiers `pages/`, `actions/`, etc. |
| 9 | **[05-Deploiement-Windows-Server-2019.md](./05-Deploiement-Windows-Server-2019.md)** | **Installation sur serveur** (IIS, PHP, SQL Server) |
| 10 | **[09-Connexion-base-de-donnees.md](./09-Connexion-base-de-donnees.md)** | Chaîne de connexion PHP ↔ SQL Server |
| 11 | **[04-Technical-Glossary.md](./04-Technical-Glossary.md)** | Glossaire |
| 12 | **[THESIS_ARCHITECTURE.md](./THESIS_ARCHITECTURE.md)** | Synthèse « soutenance » (résumé unique) |

## Fichiers hors de `docs/`

- **`CONNECTION_STRINGS.md`** (à la racine) : exemples de chaînes de connexion SQL Server / PHP (partiellement en anglais technique ; à utiliser avec **`includes/db.php`**).
- **`memoire-pfe/`** : le mémoire PDF attendu par les enseignants — **référence académique** ; les `docs/` en sont la **vulgarisation opérationnelle**.

## En cas de doute

1. Relire le **chapitre 4** du mémoire (réalisation / déploiement) — aligné avec **`05-Deploiement-Windows-Server-2019.md`**.
2. Vérifier **`06-Structure-du-projet-et-roles.md`** pour savoir quel fichier modifier.
3. Vérifier **`02-Ticket-Lifecycle.md`** et **`08-Securite-et-regles-metier.md`** pour le comportement métier attendu.
