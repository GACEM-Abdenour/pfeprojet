# Connexion à la base de données — PHP et SQL Server

**Objectif :** expliquer **simplement** comment l’application se connecte à **Microsoft SQL Server**, en cohérence avec le mémoire et le fichier **`includes/db.php`**. Pour plus de détails techniques (PowerShell, SSMS, `sqlcmd`), voir aussi **`CONNECTION_STRINGS.md`** à la **racine** du dépôt.

**Avec Docker :** serveur = `db`, utilisateur SQL = `sa`, mot de passe = variable **`MSSQL_SA_PASSWORD`** (voir **`10-Comptes-demonstration-et-Docker.md`**). Le fichier **`includes/config.docker.php`** active **`TrustServerCertificate`** pour le conteneur SQL Server.

---

## 1. Informations dont vous avez besoin

| Élément | Exemple courant (développement) |
|---------|--------------------------------|
| **Serveur / instance** | `localhost\SQLEXPRESS` ou `.\SQLEXPRESS` |
| **Nom de la base** | `GIA_IncidentDB` |
| **Authentification** | Souvent **Windows** (compte Windows du service IIS ou de votre session) ; parfois login SQL séparé |

---

## 2. Rôle de PDO

PHP utilise **PDO** (PHP Data Objects) avec le pilote **`sqlsrv`** pour parler à SQL Server. Les requêtes passent par des **paramètres** (placeholders) pour limiter les risques d’injection SQL — voir **`08-Securite-et-regles-metier.md`**.

---

## 3. Exemple de chaîne DSN (à adapter)

Format fréquent avec PDO :

```text
sqlsrv:Server=localhost\SQLEXPRESS;Database=GIA_IncidentDB;CharacterSet=UTF-8
```

Avec **authentification Windows intégrée**, le nom d’utilisateur et le mot de passe peuvent être **`null`** dans l’appel PDO (selon la configuration exacte du serveur).

---

## 4. Où c’est configuré dans le projet ?

- Fichier **`includes/db.php`** (ou fichier d’exemple **`includes/config.example.php`** si vous dupliquez avant de commiter des secrets).
- Les constantes du type **`DB_HOST`**, **`DB_NAME`**, **`DB_USE_WINDOWS_AUTH`** doivent correspondre à votre **instance SQL** réelle.

---

## 5. Vérifier que ça fonctionne

1. La base **`GIA_IncidentDB`** existe (créée par script ou par **`setup_database.php`** / PowerShell selon le dépôt).
2. Les extensions PHP **`sqlsrv`** et **`pdo_sqlsrv`** sont **chargées** (voir **`05-Deploiement-Windows-Server-2019.md`**).
3. Une page de test (temporaire) ou une action réelle (login) réussit sans erreur PDO.

---

## 6. Erreurs fréquentes

| Symptôme | Piste |
|----------|--------|
| **could not find driver** | Extensions `pdo_sqlsrv` / `sqlsrv` non installées ou non activées dans `php.ini`. |
| **Login failed** | Mauvaise instance, ou compte IIS sans droit sur SQL Server (authentification Windows). |
| **Base introuvable** | Nom de base incorrect ou schéma non créé. |

---

*Document de vulgarisation — les valeurs exactes dépendent de votre machine et du serveur cible.*
