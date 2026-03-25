# Structure du projet et rôles des dossiers — GIA

**Objectif :** permettre à un débutant de **s’orienter dans le dépôt** et de savoir **où modifier quoi**, en cohérence avec le mémoire (couche présentation / actions / logique partagée).

---

## 1. Arborescence logique (simplifiée)

À la racine du projet (dossier `sonalgaz`), on trouve typiquement :

| Emplacement | Rôle |
|-------------|------|
| **`index.php`** | Point d’entrée souvent utilisé pour rediriger vers la connexion ou le tableau de bord. |
| **`pages/`** | **Pages visibles** dans le navigateur : écrans HTML/PHP (listes, formulaires, détail d’un ticket). |
| **`actions/`** | **Traitement des formulaires** : reçoit `POST`, parle à la base, puis **`header('Location: ...')`** vers une page. **Pas de mise en page HTML lourde ici.** |
| **`includes/`** | **Composants réutilisables** : connexion BD (`db.php`), fonctions (`functions.php`), en-tête, pied de page, barre latérale, garde-fous d’authentification. |
| **`uploads/`** | Stockage des **fichiers joints** aux tickets (droits d’écriture IIS à configurer — voir `05-Deploiement-Windows-Server-2019.md`). |
| **`test/`** (si présent) | Scripts de test ou données de démo — **ne pas** laisser en production sans contrôle. |
| **`database/`** ou scripts **`setup_*.ps1` / `setup_database.php`** | Création du schéma et parfois données initiales. |

> Le mémoire parle aussi de noms de fichiers comme **`submit_ticket.php`**, **`update_ticket.php`** : dans ce dépôt, ils se trouvent sous **`actions/`** (à vérifier dans le code réel).

---

## 2. Qui appelle qui ? (flux simple)

1. L’utilisateur ouvre une page dans **`pages/`** (ex. formulaire de connexion, liste des tickets).
2. Le formulaire envoie les données vers un script dans **`actions/`** (ex. `login_action.php`, `submit_ticket.php`).
3. L’action utilise **`includes/db.php`** pour la base et **`includes/functions.php`** (ou équivalent) pour les règles communes (journalisation, etc.).
4. L’action **redirige** vers une page dans **`pages/`** avec un message dans l’URL ou en session.

Ainsi, **`pages/`** = « ce qu’on voit », **`actions/`** = « ce qu’on fait en coulisse ».

---

## 3. Fichiers souvent importants

| Fichier | Utilité |
|---------|---------|
| **`includes/db.php`** | Connexion PDO à **SQL Server** ; constantes de connexion. |
| **`includes/functions.php`** | Fonctions métier (ex. journal `incident_logs`, helpers). |
| **`includes/auth.php`** ou logique dans `functions.php` | Vérifier si l’utilisateur est connecté / a le bon rôle. |
| **`includes/header.php`**, **`sidebar.php`**, **`footer.php`** | Mise en page commune ; la sidebar affiche souvent des menus **selon le rôle**. |
| **`pages/view_ticket.php`** | **Détail d’un ticket** + historique + formulaire de mise à jour pour les techniciens. |
| **`actions/update_ticket.php`** | Mise à jour statut / commentaires / assignation selon les règles. |

---

## 4. Rôles utilisateurs (rappel)

Aligné avec le mémoire et **`02-Ticket-Lifecycle.md`** :

| Rôle | En base (`users.role`) | Capacités typiques |
|------|------------------------|---------------------|
| Déclarant | `Reporter` | Créer un ticket, voir **ses** tickets. |
| Technicien | `Technician` | Voir file d’attente, s’assigner, changer statuts, commenter. |
| Administrateur | `Admin` | Vue globale, KPIs, gestion utilisateurs (selon implémentation), réassignation. |

Le code doit **refuser** l’accès aux pages ou actions si le rôle ne convient pas (voir **`08-Securite-et-regles-metier.md`**).

---

## 5. Cohérence avec le mémoire

- **Chapitre Conception / Réalisation** : architecture **3-tiers** → ici, **`pages/`** + assets = présentation ; **`actions/`** + **`includes/`** = logique ; **SQL Server** = données.
- **Chapitre Analyse** : cas d’utilisation → se traduisent par des **pages** et des **actions** nommées dans ce document.

---

*À mettre à jour si l’arborescence du dépôt évolue (refactorisation, renommage).*
