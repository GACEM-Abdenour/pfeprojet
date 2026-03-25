# Contexte métier et alignement avec le mémoire (`memoire-pfe/`)

**Objectif :** expliquer **en quelques pages** le **cadre Naftal / Branche Carburants** et indiquer **quel chapitre du mémoire** correspond à **quel document** dans `docs/`, pour que les étudiants sachent **quoi maîtriser** pour l’oral et la reprise technique du projet.

---

## 1. De quoi parle le mémoire ?

Le mémoire décrit la **conception et la réalisation** d’une plateforme web **GIA** (Gestion des Incidents Applicatifs) pour le **Groupe Informatique de la Branche Carburants** de **Naftal** : centraliser les déclarations d’incidents, les assigner aux techniciens, suivre les **statuts**, produire des **indicateurs** (KPIs) et garder une **trace** de chaque action (**journal d’audit**).

**Naftal** est une filiale de Sonatrach ; le mémoire situe l’entreprise, son système d’information et le rôle de la **DCSI** (Direction centrale des systèmes d’information). **GIA** ne remplace pas la DCSI : c’est un outil de **support** au niveau de la branche, cohérent avec la modernisation du SI.

---

## 2. Correspondance chapitres du mémoire ↔ documentation `docs/`

| Chapitre du mémoire (thème) | Contenu principal | Document(s) `docs/` à lire |
|----------------------------|-------------------|----------------------------|
| Introduction | Problématique, objectifs GIA | Ce fichier + **`README.md`** |
| Cadre théorique (SI, MEF, fonction support) | Pourquoi un outil de ticketing a du sens | **`THESIS_ARCHITECTURE.md`** (résumé) ; le détail est dans le PDF du mémoire |
| Contexte Naftal, DCSI, SWOT, existant | Pourquoi l’ancien processus (téléphone, mails) posait problème | Ce fichier (résumé) ; détail dans le PDF |
| Analyse des besoins | Acteurs, exigences, UML, règles | **`02-Ticket-Lifecycle.md`**, **`08-Securite-et-regles-metier.md`** |
| Conception | Architecture 3-tiers, cycle de vie, ERD | **`01-System-Architecture.md`**, **`03-Database-Schema-and-ERD.md`** |
| Réalisation | Windows Server 2019, IIS, PHP, SQL Server, captures | **`05-Deploiement-Windows-Server-2019.md`**, **`06-Structure-du-projet-et-roles.md`** |
| Conclusion / perspectives | Pistes (e-mail, FAQ, multi-branches) | Fin du mémoire PDF ; pas dupliqué ici |
| Annexes (glossaire, MEF) | Définitions | **`04-Technical-Glossary.md`** |

---

## 3. Ce que les enseignants attendent en général

- **Compréhension métier** : qui crée un ticket, qui le traite, pourquoi la **traçabilité** compte.
- **Compréhension technique** : **3 couches**, **sessions**, **rôles**, **PDO**, **SQL Server**, **déploiement IIS**.
- **Cohérence** : ce qui est écrit dans le mémoire doit **coller** au code et à la base (statuts, tables, journal).

Les fichiers **`docs/`** servent de **aide-mémoire** ; le mémoire PDF reste la **référence officielle** pour le jury.

---

## 4. Glossaire minimal « oral »

| Terme | Une phrase |
|-------|------------|
| **GIA** | Plateforme de gestion des incidents applicatifs (tickets). |
| **DCSI** | Direction qui pilote le système d’information corporate chez Naftal. |
| **3-tiers** | Interface → PHP → base de données ; la base n’est pas exposée au navigateur. |
| **`incident_logs`** | Journal des actions sur un ticket (qui, quoi, quand). |
| **IIS** | Serveur web Windows qui exécute PHP via FastCGI. |

---

*Pour le détail historique et organisationnel (chiffres, SWOT, missions DCSI), se reporter au chapitre « Contexte » du mémoire.*
