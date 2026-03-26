# Documentation des diagrammes PlantUML (GIA)

Ce document explique les 3 fichiers PlantUML créés pour le projet **GIA** :

- `usecase_gia.puml` : diagramme de cas d'utilisation (fonctionnel)
- `sequence_gia.puml` : diagramme de séquence (scénarios d'exécution)
- `class_gia.puml` : diagramme de classes (structure applicative / modèle conceptuel)

Ces diagrammes sont cohérents avec les rôles et le cycle de vie des incidents définis dans le mémoire.

---

## 1) Diagramme de cas d'utilisation : `usecase_gia.puml`

### Acteurs
- `Employé (E)` (Reporter / Demandeur)
  - déclare des incidents (création de ticket)
  - consulte l'état de ses propres requêtes (tickets du demandeur)
- `Technicien (T)`
  - prend en charge les tickets
  - consulte la file d'attente (tickets ouverts / non assignés)
  - fait évoluer le statut et clôture quand c'est autorisé
- `Administrateur (A)`
  - gère la supervision globale
  - gère les utilisateurs
  - agit sur l'assignation/réassignation et suit les KPI
  - peut aussi intervenir sur les statuts quand les règles métier le permettent

### Cas d'utilisation inclus dans le rectangle "Plateforme GIA"
- `S'authentifier` : connexion et redirection selon le rôle.
- `Créer un ticket` : déclaration structurée d'un incident.
- `Consulter ses tickets` : consultation des tickets appartenant au demandeur.
- `Consulter la file d'attente` : vue des tickets non assignés pour le technicien.
- `Prendre en charge un ticket` : assignation d'un ticket au technicien.
- `Changer le statut` : transitions dans le cycle de vie.
- `Clôturer un ticket` : clôture finale (statut final selon le workflow).
- `Gérer les utilisateurs` : administration des comptes / rôles.
- `Assigner / Réassigner un ticket` : prise en charge forcée ou réassignation.
- `Consulter les KPIs` : tableau de bord administrateur (agrégats sur incidents).

### Lecture
Les flèches `Acteur --> Cas d'utilisation` indiquent qui a le droit fonctionnel d'exécuter la capacité correspondante.

---

## 2) Diagramme de séquence : `sequence_gia.puml`

> Le diagramme décrit un enchaînement partiel, centré sur les 3 moments clés : **authentification**, **soumission d'un ticket**, **changement de statut**.

### Participants
- `Employé (E)` : utilisateur demandeur
- `Technicien (T)` : intervenant support
- `Interface Web (IW)` : pages / formulaires
- `Contrôleur PHP (CP)` : logique métier côté serveur (vérifications, règles R1-R7)
- `Base de Données (BD)` : tables `users`, `incidents`, `incident_logs` (et éventuellement pièces jointes)

### Scénario 1 : Authentification
1. `E -> IW` : saisie identifiant + mot de passe.
2. `IW -> CP` : `POST /login`.
3. `CP -> BD` : requête `SELECT` sur `users` (récupération user + rôle).
4. `CP --> IW` : création de session.
5. `IW --> E` : redirection vers le tableau de bord.

### Scénario 2 : Soumission d'un ticket
1. `E -> IW` : remplissage du formulaire ticket.
2. `IW -> CP` : `POST /ticket/create`.
3. `CP -> CP` : validation des données (contrôles côté serveur).
4. `CP -> BD` : insertion dans `incidents` (création du ticket, statut initial `Ouvert`).
5. `BD --> CP` : confirmation + identifiant du ticket.
6. `CP -> BD` : insertion d'une entrée dans `incident_logs` (action `Creation`).
7. `CP --> IW` puis `IW --> E` : retour utilisateur et affichage du numéro de ticket au format `INC-AAAA-NNNNN`.

### Scénario 3 : Changement de statut
1. `T -> IW` : ouvre/consulte un ticket assigné.
2. `IW -> CP` : `GET /ticket/{id}`.
3. `CP -> BD` : récupération des données du ticket.
4. `T -> IW` : choisit une transition de statut.
5. `IW -> CP` : `PUT /ticket/{id}/status`.
6. `CP -> BD` : mise à jour du champ `status` dans `incidents`, puis insertion d'un log dans `incident_logs`.
7. `BD --> CP` puis `CP --> IW` : la vue ticket est mise à jour (timeline reconstituée).

> Dans ce diagramme, la transition illustrée passe par le statut `Diagnostic` (cohérent avec le workflow défini).

---

## 3) Diagramme de classes : `class_gia.puml`

### But du diagramme
Ce diagramme modélise :
- les **entités** et **valeurs** (rôles, priorités, statuts),
- la **structure des informations** manipulées par GIA (utilisateurs, tickets, logs, pièces jointes),
- et une couche de **services** applicatifs (auth et gestion des tickets).

### Enums (valeurs contrôlées)
- `Role` : `Reporter`, `Technician`, `Admin`
- `Priority` : `Critical`, `Major`, `Minor`
- `Status` : `Ouvert`, `Assigne`, `Diagnostic`, `Resolu`, `Clos`, `Bloque`

Ces enums correspondent aux choix contrôlés côté application et/ou base de données.

### Classes principales
- `User`
  - correspond à la table `users`
  - contient les informations d'identité + rôle + département
- `Incident`
  - correspond à la table `incidents`
  - contient l'identifiant, le titre/description, la priorité, le statut, les dates
  - inclut aussi une propriété `ticketNumber` (format `INC-AAAA-NNNNN`) utilisée au niveau de présentation
- `IncidentLog`
  - correspond à la table `incident_logs`
  - garde la trace des actions horodatées
- `Attachment`
  - correspond à la table `attachments`
  - représente les fichiers joints à un incident (chemin/nom et date d'envoi)

### Services applicatifs (niveau conception)
- `AuthService`
  - `login`, `logout`, `requireRole`
- `TicketService`
  - `createTicket`, `assignTicket`, `updateStatus`, `reassignTicket`

Ces services ne sont pas des tables : ils représentent le rôle des contrôleurs/scripts PHP dans la conception.

### Relations (structure logique)
- `User` -> `Incident` : création (`cree`)
- `User` -> `Incident` : assignation (`assigne_a`)
- `Incident` -> `IncidentLog` : traçabilité (`trace`)
- `User` -> `IncidentLog` : auteur d'une action
- `Incident` -> `Attachment` : pièces jointes associées

### Remarque (alignement strict DB)
Le diagramme est volontairement “conception/logiciel”. Pour une version **100% base de données** (MCD/MLD stricte), on peut :
- ajouter explicitement dans `IncidentLog` les champs FK `incident_id` et `user_id`,
- modéliser `Category` comme une entité dédiée au lieu d'un simple attribut texte,
- harmoniser exactement les libellés avec le MLD (`Assigné`, etc.).

