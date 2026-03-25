# Sécurité et règles métier — version simple (alignée mémoire)

**Objectif :** résumer **sans jargon inutile** les règles que le système doit respecter — telles qu’énoncées dans le mémoire (chapitre Analyse / Conception) et implémentées en PHP / SQL.

---

## 1. Principes de sécurité

| Principe | Explication courte |
|----------|-------------------|
| **Sessions** | Après connexion, le serveur « sait » qui vous êtes pendant un temps limité (`$_SESSION`). |
| **Contrôle d’accès par rôle** | Seuls certains rôles peuvent ouvrir certaines pages ou actions (ex. un **Reporter** ne modifie pas les tickets des autres). |
| **Mots de passe** | Stockés en **hash** dans la table `users`, jamais en clair. |
| **Requêtes SQL** | Utiliser des **paramètres** (PDO) pour limiter le risque d’**injection SQL**. |
| **Fichiers uploadés** | Dossier dédié (`uploads/`), droits serveur restreints, pas d’exécution de scripts uploadés comme du PHP si la config le permet. |

---

## 2. Règles de gestion (équivalent R1–R7 du mémoire)

Ces règles décrivent le **comportement attendu** du système ; elles servent de **checklist** pour les tests et la soutenance.

| ID | Règle (formulation simple) |
|----|----------------------------|
| **R1** | Un **nouveau** ticket est **ouvert** (`Open`) et **personne n’est assigné** au départ (`assigned_to` vide / NULL). |
| **R2** | Seuls **Technicien** ou **Admin** font avancer un ticket dans les statuts « métier » (assignation, diagnostic, clôture…). |
| **R3** | Un **Reporter** ne voit que **ses propres** tickets (filtrage sur le créateur). |
| **R4** | Pour **clôturer** ou déclarer un **échec**, un **commentaire** est exigé (traçabilité humaine). |
| **R5** | Si le statut redevient **Open** (retour en file), l’assignation est **annulée** (le technicien n’est plus lié au ticket). |
| **R6** | Les changements importants laissent une trace dans **`incident_logs`** (horodatage + auteur). |
| **R7** | L’**Admin** peut **réassigner** un ticket même s’il était déjà pris en charge. |

Les libellés exacts des statuts en base peuvent être en anglais (`Open`, `Assigned`, …) — voir **`02-Ticket-Lifecycle.md`**.

---

## 3. Lien avec la traçabilité

La table **`incident_logs`** répond à des questions du type :

- Qui a fermé le ticket n° X ?
- Quand le statut est passé de *Diagnostic* à *Resolved* ?

Sans ce journal, on retombe sur le problème du mémoire : support **oral** ou par mails **non structurés**, peu auditable.

---

## 4. Où c’est dans le code ?

En pratique : **`actions/update_ticket.php`**, **`actions/submit_ticket.php`**, et les fonctions communes dans **`includes/functions.php`** (souvent une fonction du type « enregistrer une ligne de journal »). La liste exacte des fichiers peut évoluer : vérifier le dépôt et **`06-Structure-du-projet-et-roles.md`**.

---

*Document pédagogique — à synchroniser avec le code en cas d’évolution des règles métier.*
