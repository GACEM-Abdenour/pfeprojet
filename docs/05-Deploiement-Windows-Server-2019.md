# Déploiement sur Windows Server 2019 — IIS, PHP et SQL Server

**Objectif :** donner **toutes les étapes** pour reproduire l’environnement décrit dans le mémoire (**Windows Server 2019**, **IIS**, **PHP 8**, **Microsoft SQL Server Express**, **FastCGI**), en langage accessible. Ce guide complète le chapitre « Réalisation » du mémoire et la synthèse `THESIS_ARCHITECTURE.md`.

---

## 1. Pourquoi Windows Server 2019 et pas un simple PC ?

| Idée simple | Détail |
|-------------|--------|
| **Disponibilité** | Un serveur d’entreprise est prévu pour tourner **24h/24** ; un PC portable est souvent éteint ou en veille. |
| **Plusieurs utilisateurs** | Plusieurs personnes accèdent **en même temps** au même site (techniciens, déclarants, admin). |
| **Réseau entreprise** | L’application reste dans le **réseau interne** (intranet), avec pare-feu et sauvegardes gérés par l’IT. |
| **Standards Naftal** | Le mémoire cible un déploiement **aligné** sur l’infrastructure type du cahier des charges (IIS, SQL Server). |

En **développement**, on peut utiliser **Windows 11** + PHP en local ; en **production**, le mémoire parle de **Windows Server 2019** + **IIS**.

> **Alternative pour tester sans IIS :** le dépôt propose **Docker** (PHP + Apache + SQL Server Linux) — voir **`10-Comptes-demonstration-et-Docker.md`**. Ce n’est pas identique à la stack « Naftal » du mémoire, mais permet de faire tourner l’application rapidement.

---

## 2. Machine virtuelle (recommandé pour les tests)

Pour **ne pas toucher** au réseau de production, le mémoire mentionne une **machine virtuelle** (ex. **VirtualBox**) avec **Windows Server 2019** installé comme invité.

1. Créer une VM avec suffisamment de RAM et de disque (SQL Server + IIS + PHP).
2. Installer **Windows Server 2019**, les **mises à jour**, et rejoindre le réseau de test si besoin.
3. Noter le **nom de la machine** et l’adresse **IP** pour y accéder depuis le navigateur (`http://IP/...`).

---

## 3. Vue d’ensemble des couches à installer

```
Navigateur (clients)
       ↓ HTTP
  IIS (serveur web)
       ↓ FastCGI
  PHP (logique applicative)
       ↓ pilote SQL
  Microsoft SQL Server (données)
```

Rien n’expose la base **directement** au navigateur : tout passe par **PHP** sous **IIS**.

---

## 4. Phase A — Installer et configurer IIS avec CGI

1. Ouvrir le **Gestionnaire de serveur** → **Ajouter des rôles et fonctionnalités**.
2. Choisir **Serveur Web (IIS)**.
3. Cocher au minimum les services communs HTTP et la prise en charge **CGI** (nécessaire pour relier IIS à PHP via **FastCGI**).
4. Valider l’installation ; vérifier que la page d’accueil IIS par défaut s’affiche sur `http://localhost`.

---

## 5. Phase B — Installer PHP (version NTS) et le lier à IIS

1. Télécharger **PHP pour Windows** en version **Non Thread Safe (NTS)** — c’est la variante adaptée à **FastCGI** avec IIS.
2. Décompresser les fichiers dans un dossier fixe, par exemple **`C:\PHP`** (ou un chemin imposé par l’organisation).
3. Copier **`php.ini-production`** vers **`php.ini`** et l’éditer :
   - activer les extensions utiles : `openssl`, `curl`, `mbstring`, `fileinfo` ;
   - plus tard : **`sqlsrv`** et **`pdo_sqlsrv`** (après installation des pilotes Microsoft).
4. Ajuster **`upload_max_filesize`** et **`post_max_size`** si les pièces jointes des tickets doivent dépasser les valeurs par défaut.
5. Dans **IIS** (Gestionnaire des services Internet) :
   - ouvrir **Mappages de gestionnaire** ;
   - ajouter une règle pour **`*.php`** → module **FastCgiModule**, exécutable **`php-cgi.exe`**, avec l’argument **`-c`** pointant vers **`php.ini`**.
6. **Recycler** le pool d’applications après chaque changement important de `php.ini`.

À ce stade, un fichier **`info.php`** contenant `<?php phpinfo(); ?>` peut confirmer que PHP tourne — **à supprimer en production** (fuite d’informations).

---

## 6. Phase C — Microsoft SQL Server Express et base GIA

1. Installer **SQL Server Express** (ou une édition validée par l’entreprise).
2. Activer le protocole **TCP/IP** si les connexions ne passent pas seulement en mémoire partagée (selon la configuration).
3. Créer la base **`GIA_IncidentDB`** (ou le nom défini dans le projet).
4. Exécuter le script de schéma du dépôt (voir **`database/`** à la racine du projet si présent, ou **`setup_database.php`** / scripts PowerShell selon votre livrable).
5. Installer le **pilote ODBC Microsoft pour SQL Server** si demandé par l’environnement.

---

## 7. Phase D — Extensions PHP `sqlsrv` et `pdo_sqlsrv`

PHP ne parle pas « tout seul » à SQL Server : il faut les DLL **Microsoft** correspondant à la **version de PHP** (thread safe ou non, x64, etc.).

1. Télécharger les **Microsoft Drivers for PHP for SQL Server** adaptés à votre version PHP.
2. Copier **`php_sqlsrv.dll`** et **`php_pdo_sqlsrv.dll`** dans le dossier **`ext`** de PHP.
3. Dans **`php.ini`**, ajouter les lignes **`extension=php_sqlsrv`** et **`extension=php_pdo_sqlsrv`** (noms exacts selon les fichiers fournis).
4. Redémarrer IIS / recycler le pool et vérifier dans **`phpinfo()`** que les extensions sont **chargées** — puis retirer **`phpinfo()`** en production.

---

## 8. Phase E — Déployer le code de l’application

1. Copier le projet sous le dossier servi par IIS, typiquement  
   **`C:\inetpub\wwwroot\sonalgaz`**  
   (le nom peut varier ; l’important est que **le site IIS** pointe vers ce répertoire).
2. Vérifier le **point d’entrée** (souvent **`index.php`** à la racine du site).
3. Configurer la connexion à la base dans **`includes/db.php`** (ou fichier d’exemple **`includes/config.example.php`** copié vers une config réelle) : serveur, nom de base, authentification Windows ou SQL selon le cas — voir aussi **`CONNECTION_STRINGS.md`** à la racine du dépôt.

---

## 9. Droits sur le dossier `uploads/` (pièces jointes)

Les tickets peuvent joindre des fichiers. Le compte sous lequel **IIS** exécute PHP (souvent via le pool d’applications, et le groupe **`IIS_IUSRS`**) doit avoir le droit d’**écrire** dans le dossier **`uploads/`** (ou équivalent défini dans le code).

- Principe du **moindre privilège** : donner **Modify / Write** sur ce dossier seulement, pas sur tout le disque.
- Si l’upload échoue, vérifier **d’abord** les droits NTFS, **ensuite** `upload_max_filesize` dans `php.ini`.

---

## 10. Contrôles après installation (checklist)

| Vérification | Pourquoi |
|--------------|----------|
| Le site répond en HTTP(S) sur l’URL prévue | IIS écoute et le site est démarré |
| **`phpinfo()`** OK puis **supprimé** | PHP + extensions chargées ; pas de fuite en prod |
| Connexion SQL depuis une page de test puis **retirée** | Pilotes `sqlsrv` / PDO opérationnels |
| Création d’un compte **Reporter**, **Technician**, **Admin** | Parcours complet des rôles |
| Création d’un ticket **avec** et **sans** pièce jointe | PHP + droits `uploads/` + SQL |
| Parcours **création → assignation → changement de statut → clôture** | Cohérence avec `02-Ticket-Lifecycle.md` |
| Sauvegarde de **`GIA_IncidentDB`** planifiée (hors code) | Continuité de service |

---

## 11. Rappels sécurité (résumé)

- Mots de passe : stockés en **hash** en base, jamais en clair.
- Requêtes SQL : **paramétrées** (PDO), pas de concaténation aveugle avec la saisie utilisateur.
- Accès aux pages : **session** + fonctions du type **`requireRole()`** selon le mémoire et le code.

Pour le détail « règles métier », voir **`08-Securite-et-regles-metier.md`**.

---

## 12. Liens avec les autres documents

- **`THESIS_ARCHITECTURE.md`** — synthèse courte (3-tiers, pourquoi serveur, cycle de vie).
- **`01-System-Architecture.md`** — même idée, un peu plus développée.
- **`06-Structure-du-projet-et-roles.md`** — où sont les fichiers dans le dépôt.
- **Mémoire `memoire-pfe/chapters/4_realisation.tex`** — version académique du même déploiement.

---

*Document rédigé pour la reprise du projet GIA — à tenir à jour si les chemins ou noms de fichiers changent dans le dépôt.*
