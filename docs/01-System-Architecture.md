# Vue d’ensemble de l’architecture système — GIA (Plateforme GIA)


**Objectif :** Expliquer la *vue d’ensemble* en termes simples pour la soutenance : comment l’interface utilisateur, la logique applicative et la base de données fonctionnent ensemble.


---


## 1. Architecture en trois couches (ce que ça signifie)


Cette application suit une **architecture en 3 couches**. Imagine trois couches qui ont chacune un rôle bien défini :


| Couche | Nom (technique) | Langage courant |
|--------|------------------|-----------------|
| **1** | **Présentation / Frontend** | Ce que l’utilisateur *voit* et sur quoi il *clique* (écrans, formulaires, boutons). |
| **2** | **Application / Backend** | Les *règles* et les *flux de travail* (qui peut faire quoi, comment un ticket est mis à jour). |
| **3** | **Données / Base de données** | Là où l’information est *stockée* de façon sûre et durable. |


Ces couches sont **séparées** pour que l’on puisse changer l’apparence de l’application sans casser les règles, et changer les règles sans perdre les données, tant que chaque couche communique avec la suivante de manière contrôlée.


---


## 2. La métaphore du restaurant


| Couche | Métaphore | Dans GIA |
|--------|-----------|----------|
| **Frontend** | **Le serveur** (le garon) | Prend votre commande, apporte le menu, montre l’assiette. Il ne cuisine pas et ne stocke pas la nourriture en gros. |
| **Backend (PHP)** | **La cuisine** | Reçoit la commande, applique les recettes (règles métier), coordonne qui prépare quoi. |
| **Base de données (SQL Server)** | **Le garde‑manger / stock** | Contient tous les ingrédients et les plats finis *enregistrés* : utilisateurs, tickets, journaux. Rien n’est “perdu en cuisine” sauf si les règles le permettent. |


- Le **client** (l’utilisateur) ne parle qu’au **serveur** (navigateur + pages HTML/CSS/Bootstrap).
- Le **serveur** envoie des requêtes à la **cuisine** (scripts PHP et `pages/*.php`).
- La **cuisine** lit et écrit les ingrédients dans le **garde‑manger** (SQL Server via PDO).


---


## 3. Ce que chaque couche utilise dans ce projet


### Frontend (le “visage”)


- **HTML** — structure des pages (titres, tableaux, formulaires).
- **CSS** (dont Bootstrap et des styles personnalisés) — mise en page, couleurs, responsivité.
- **JavaScript** (par ex. DataTables, graphiques) — tableaux et tableaux de bord plus riches dans le navigateur.


Le navigateur **affiche** les données ; il ne conserve **pas** la copie officielle des tickets ou des mots de passe. Après la connexion, il montre ce que le serveur lui envoie.


### Backend (le “cerveau”)


- **PHP** s’exécute sur le **serveur** (par exemple avec le serveur web intégré de PHP ou IIS/Apache).
- **Responsabilités clés :**
  - **Authentification :** connexion, session (`$_SESSION`), déconnexion.
  - **Autorisation :** `requireLogin()`, `requireRole('Admin'|'Technician'|'Reporter')` — seul le bon rôle peut ouvrir certaines pages ou effectuer certaines actions.
  - **Logique métier :** création d’un ticket, affectation d’un technicien, changement de statut, écriture dans `incident_logs`.
- Les **actions** se trouvent dans des fichiers comme `actions/update_ticket.php`, `actions/submit_ticket.php` — elles traitent les requêtes POST et effectuent une redirection ou une redirection avec erreurs.


### Base de données (la “mémoire”)


- **Microsoft SQL Server** (Express dans ce projet) stocke les tables : `users`, `incidents`, `incident_logs`, `attachments`, etc.
- **PDO** (PHP Data Objects) est le **pont sécurisé** entre PHP et SQL Server : les requêtes paramétrées aident à prévenir les injections SQL.


> **Remarque :** Le guide que vous avez pu voir mentionne parfois “MySQL” ; **ce projet est implémenté avec SQL Server**. L’*idée* d’une base de données relationnelle reste la même : tables, clés, relations.


---


## 4. Comment les parties “se parlent” (flux de requête)


Flux typique lorsqu’un utilisateur **met à jour un ticket** :


1. L’utilisateur remplit un formulaire sur une **page** (par ex. `pages/view_ticket.php`) et clique sur Envoyer.
2. Le navigateur envoie une requête **HTTP POST** à un script **d’action** (par ex. `actions/update_ticket.php`).
3. PHP vérifie la **session** et le **rôle**, puis exécute du **SQL** (transaction + `UPDATE` sur `incidents`).
4. PHP insère une ligne dans **`incident_logs`** pour la traçabilité.
5. PHP envoie une **redirection** vers la page du ticket avec un message de succès ou d’erreur dans l’URL.


Donc : **Navigateur → PHP → Base de données → PHP → Navigateur.** La base de données n’est jamais exposée directement à l’utilisateur final.


---


## 5. Un diagramme (modèle mental)



```
[ Utilisateur / Navigateur ]
|
v
HTML / CSS / JS (Présentation)
|
v
Pages & actions PHP (Application / règles métier)
|
v
PDO -----------------> SQL Server
(users, incidents, incident_logs, …)


```


---


## 6. Pourquoi c’est important pour la thèse


- Vous pouvez dire : **« Nous avons séparé les responsabilités : l’interface présente l’information ; PHP fait respecter le workflow et la sécurité ; la base de données garantit la persistance et la traçabilité. »**
- Vous pouvez défendre la **scalabilité** et la **maintenance** : de nouveaux rapports ou écrans peuvent réutiliser les mêmes règles de backend et de base de données.


---


*Document aligné avec la base de code GIA : PHP + Bootstrap + SQL Server.*
