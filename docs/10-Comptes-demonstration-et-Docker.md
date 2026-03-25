# Comptes de démonstration et déploiement Docker

Ce document répond à deux besoins : **se connecter à l’application** avec des identifiants connus, et **lancer tout le projet avec Docker** (PHP + SQL Server) sans installer IIS à la main.

> **Avertissement sécurité :** le mot de passe de démonstration est **unique et faible** (`password`) — **uniquement pour la démo et le développement**. En production (Naftal ou autre), utilisez des politiques de mot de passe strictes et ne commitez **jamais** de secrets dans Git.

---

## 1. Comptes utilisateurs (après insertion des données de test)

Ces comptes sont créés par le script **`test/insert_test_data.php`** (ou automatiquement au premier démarrage du conteneur **web** Docker).

**Mot de passe pour tous les comptes ci-dessous : `password`**

| Rôle | Nom d’utilisateur |
|------|-------------------|
| **Administrateur** | `admin` |
| **Technicien** | `tech1` |
| **Technicien** | `tech2` |
| **Déclarant (Reporter)** | `reporter1` |
| **Déclarant (Reporter)** | `reporter2` |

**Page de connexion :** `pages/login.php` (ou racine du site qui redirige vers la connexion).

### Si la connexion échoue

1. Vérifier que la base **`GIA_IncidentDB`** existe et que le schéma est appliqué (`setup_database.php`).
2. Exécuter manuellement :  
   `php test/insert_test_data.php`  
   (depuis la racine du projet, avec PHP et SQL Server accessibles comme dans `includes/config.php`.)

> Si des utilisateurs avaient été créés avec d’anciens mots de passe, supprimer les lignes dans **`users`** ou réinsérer après vidage, puis relancer **`insert_test_data.php`** pour recalculer les hash avec `password`.

---

## 2. Déploiement avec Docker (recommandé pour découvrir le projet)

Le dépôt contient :

- **`Dockerfile`** — image **PHP 8.2 + Apache** avec extensions **`pdo_sqlsrv`** / **`sqlsrv`** et pilote ODBC Microsoft.
- **`docker-compose.yml`** — services **`web`** (application) et **`db`** (SQL Server 2022).
- **`includes/config.docker.php`** — copié en **`config.php`** dans l’image pour utiliser l’authentification **SQL** (`sa`) au lieu de Windows.
- **`docker/entrypoint.sh`** — attend que SQL Server réponde, lance **`setup_database.php`** puis **`insert_test_data.php`**, puis démarre Apache.

### Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (ou Docker Engine + Compose v2) installé.
- Au moins **~2 Go de RAM** libres pour SQL Server.

### Étapes

1. À la racine du projet, copier l’exemple d’environnement :  
   ```bash
   cp .env.example .env
   ```
2. Ouvrir **`.env`** et définir **`MSSQL_SA_PASSWORD`** avec un mot de passe **fort** (obligatoire pour SQL Server : majuscule, minuscule, chiffre, symbole, 8+ caractères).  
   L’exemple fourni est : `GiaDemo#2025Sql` — vous pouvez le garder pour un test local uniquement.
3. Construire et démarrer :  
   ```bash
   docker compose up --build
   ```
4. Ouvrir un navigateur : **[http://localhost:8080](http://localhost:8080)**  
   (port modifiable avec **`WEB_PORT`** dans `.env`.)

### Identifiants base de données (Docker)

- **Serveur** (depuis le conteneur web) : `db`
- **Utilisateur SQL** : `sa`
- **Mot de passe** : la même valeur que **`MSSQL_SA_PASSWORD`** dans **`.env`**

Ces valeurs sont injectées dans l’application via les variables d’environnement du service **`web`** dans **`docker-compose.yml`**.

### Données persistantes

- Les fichiers SQL Server sont stockés dans le volume Docker **`gia_mssql_data`**.
- Les **pièces jointes** uploadées sont dans le volume **`gia_uploads`** (monté sur **`/var/www/html/uploads`** dans le conteneur).

### Arrêter les conteneurs

```bash
docker compose down
```

Pour tout supprimer y compris les volumes (base effacée) :

```bash
docker compose down -v
```

---

## 3. Déploiement « classique » (Windows Server 2019 + IIS)

Pour un environnement aligné sur le **mémoire** (IIS, FastCGI, SQL Server sur Windows), suivre :

- **`05-Deploiement-Windows-Server-2019.md`**

Docker et IIS sont deux options **différentes** ; le mémoire académique décrit surtout **IIS + Windows Server**.

---

## 4. Cohérence avec le mémoire (`memoire-pfe/`)

| Sujet du mémoire | Où c’est documenté ici |
|------------------|-------------------------|
| Architecture 3-tiers | `01-System-Architecture.md`, `THESIS_ARCHITECTURE.md` |
| Cycle de vie des tickets | `02-Ticket-Lifecycle.md` |
| Schéma BDD | `03-Database-Schema-and-ERD.md` |
| Déploiement Windows / IIS | `05-Deploiement-Windows-Server-2019.md` |
| **Comptes de test + Docker** | **Ce fichier** |

---

*Les identifiants correspondent au script `test/insert_test_data.php` — mot de passe unique : **`password`**.*
