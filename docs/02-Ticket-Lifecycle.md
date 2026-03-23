# Documentation du cycle de vie d’un ticket — GIA


**Objectif :** Décrire la **logique métier** d’un ticket depuis sa création jusqu’à sa clôture, dans des termes compréhensibles pour des lecteurs non spécialistes en informatique. C’est l’élément central pour la soutenance de thèse.


---


## 1. Acteurs (qui fait quoi ?)


| Rôle | Actions typiques |
|------|------------------|
| **Reporter** | Crée des tickets (déclare un incident), consulte son propre historique. |
| **Technicien** | Prend les tickets non assignés, travaille dessus, met à jour le statut et les commentaires. |
| **Admin** | Vue globale, peut assigner ou désassigner des tickets aux techniciens, supervision. |


Les permissions sont appliquées en PHP (`requireRole`, `requireLogin`) et se reflètent dans les tableaux de bord et les boutons visibles pour chaque utilisateur.


---


## 2. Statuts (dans quel état est le ticket ?)


Le champ `incidents.status` utilise une liste fixe (voir les contraintes de base de données). Voici la **signification** en termes métier :


| Statut | Signification |
|--------|---------------|
| **Open** | Le ticket **existe** mais **aucun technicien n’en est responsable** pour l’instant (ou il a été remis dans le pool). |
| **Assigned** | Un technicien est **lié** au ticket (`assigned_to` pointe vers un utilisateur). Souvent défini lorsqu’une personne prend le ticket ou qu’un admin l’assigne. |
| **Diagnostic** | Le travail est **en cours** (investigation, analyse, corrections en cours). |
| **Resolved** | Le problème est **corrigé** du point de vue IT ; peut attendre une confirmation ou une clôture. |
| **Closed** | Le ticket est **terminé** administrativement (souvent avec `closed_at` renseigné). |
| **Failed / Blocked** | Le ticket **ne peut pas** être mené à bien comme prévu (blocage externe, correction impossible, etc.) — cela reste un **résultat final**, ce n’est pas “ouvert”. |


> Orthographe exacte dans la base de données pour le dernier : `Failed/Blocked` (avec une barre oblique).


---


## 3. Vie typique d’un ticket (cas idéal)


1. **Naissance — Création**  
   - Un **Reporter** soumet un formulaire (`create_ticket` → `submit_ticket` / similaire).  
   - Une nouvelle ligne apparaît dans **`incidents`** (en général `status = 'Open'`, `assigned_to` vide).  
   - Une entrée est écrite dans **`incident_logs`** (par ex. type d’action **Creation**).


2. **Affectation**  
   - Soit un **Technicien** “prend” le ticket (`take_ticket`), soit un **Admin** assigne quelqu’un (`assign_tech`).  
   - `assigned_to` est renseigné ; le statut devient souvent **Assigned** s’il était **Open**.  
   - **`incident_logs`** enregistre une action **Assignment** (qui a fait quoi).


3. **Travail en cours**  
   - Le technicien peut passer le statut à **Diagnostic** pendant l’investigation.  
   - Les **changements de statut** et les **commentaires** sont journalisés (`Status Change`, `Comment`).


4. **Issue**  
   - **Resolved** ou **Closed** lorsque le travail est terminé, ou **Failed/Blocked** s’il ne peut pas être mené à bien.  
   - Pour certaines transitions, le système exige un **commentaire** (règle métier pour la traçabilité).  
   - La **clôture** peut renseigner **`closed_at`** (selon le workflow dans le code).


5. **Optionnel : retour au pool**  
   - Dans certains flux, un technicien peut remettre le statut vers **Open** et effacer l’assignation pour que le ticket revienne dans la liste des **non assignés** (les règles sont dans `update_ticket.php`).


Tout au long de ce parcours, **la ligne du ticket dans `incidents` est l’instantané actuel** ; **`incident_logs` est l’historique**.


---


## 4. Comment `incident_logs` renforce la sécurité et l’audit


La table **`incident_logs`** est une **trace de type append‑only** (de nouvelles lignes sont insérées ; l’application ne “réécrit pas l’historique” pour les opérations normales).


| Ce que ça stocke | Pourquoi c’est important |
|------------------|--------------------------|
| **Quel ticket** (`incident_id`) | Relie l’événement à un incident. |
| **Qui l’a fait** (`user_id`) | Responsabilisation — lien avec `users.id`. |
| **Quel type d’événement** (`action_type`) | Par ex. Creation, Assignment, Status Change, Comment. |
| **Détails** (`message`) | Explication lisible par un humain (facultative mais utile). |
| **Quand** (`timestamp`) | Ordonnancement et chronologie d’enquête. |


**Audit :** Si quelqu’un demande *« Qui a clôturé le ticket n°9 et quand ? »*, on interroge `incident_logs` plutôt que de faire confiance à la mémoire.


**Sécurité :** Même si l’affichage à l’écran est trompeur, le **journal** montre la séquence des actions (sous réserve de vos sauvegardes et politiques d’accès BD).


**Intégrité :** Les clés étrangères garantissent que les journaux pointent vers de vrais incidents et utilisateurs (voir la documentation du schéma).


---


## 5. Courte FAQ pour la soutenance


- **Un Reporter peut‑il modifier n’importe quel ticket ?**  
  Non — seulement les flux autorisés en PHP pour ce rôle (par ex. créer et consulter ses propres tickets).


- **Où se trouve la “vérité” de l’état courant ?**  
  Dans la ligne **`incidents`** (statut, `assigned_to`, dates).


- **Où se trouve l’histoire de ce qui s’est passé ?**  
  Dans **`incident_logs`**.


---


*Aligné avec `actions/update_ticket.php`, `actions/submit_ticket.php` et `includes/functions.php` (`logIncidentAction`).*
