# Plateforme GIA — Gestion des Incidents Applicatifs

Application web interne pour la **déclaration**, le **suivi** et la **résolution** des incidents applicatifs. Elle met en relation les **déclarants**, les **techniciens** et les **administrateurs**, avec journalisation des actions pour la **traçabilité**.

**Technologies principales :** PHP (logique métier), **Bootstrap** / HTML / CSS (interface), **Microsoft SQL Server** (données), sessions pour l’authentification.

---

## Documentation

**Toute la documentation pédagogique est en français** dans le dossier **`docs/`**.

| Document | Description |
|----------|-------------|
| **[Comptes de démonstration + Docker](docs/10-Comptes-demonstration-et-Docker.md)** | **Identifiants** (admin, technicien, reporter) et déploiement avec **Docker Compose** (PHP + SQL Server). |
| **[Lire en premier](docs/00-LIRE-EN-PREMIER.md)** | Par où commencer (ordre de lecture). |
| **[Index complet `docs/`](docs/README.md)** | Table des matières : architecture, cycle de vie, BDD, glossaire, **sécurité / règles métier**, alignement avec le **mémoire**. |
| **[Architecture — soutenance (FR)](docs/THESIS_ARCHITECTURE.md)** | Synthèse unique : 3-tiers, pourquoi **Windows Server**, cycle de vie, schéma BDD — pour un **public non technique**. |
| **[Déploiement Windows Server 2019 + IIS + PHP + SQL Server](docs/05-Deploiement-Windows-Server-2019.md)** | **Guide détaillé** d’installation serveur (correspond au chapitre *Réalisation* du mémoire). |
| **`memoire-pfe/`** | Mémoire LaTeX / PDF — **référence académique** attendue par les enseignants. |

### Docker (démarrage rapide)

```bash
cp .env.example .env
# Éditer .env : définir MSSQL_SA_PASSWORD (mot de passe fort)
docker compose up --build
```

Puis ouvrir **http://localhost:8080** et se connecter avec les comptes décrits dans **`docs/10-Comptes-demonstration-et-Docker.md`**.

---

## Exécution locale (développement)

1. **Prérequis :** PHP avec extension **PDO SQL Server** (`pdo_sqlsrv`), accès à une instance **SQL Server**. Copier `includes/config.example.php` vers `includes/config.php` et ajuster `DB_SERVER`, `DB_NAME`, etc. (après un clone, sans `config.php`, l’app charge les valeurs par défaut depuis `config.example.php`).
2. **Initialiser la base** (si besoin) : `php setup_database.php` à partir de la racine du projet.
3. **Lancer le serveur web intégré de PHP** depuis la **racine** du dépôt :

```bash
cd chemin/vers/sonalgaz
php -S localhost:8000
```

4. Ouvrir un navigateur :  
   - [http://localhost:8000/](http://localhost:8000/) (redirection vers la page de connexion), ou  
   - [http://localhost:8000/pages/login.php](http://localhost:8000/pages/login.php)

Les chemins relatifs (`pages/`, `actions/`) supposent que la **racine du site** est le dossier du projet.

> **Astuce :** pour un autre port : `php -S localhost:8080`.

---

## Structure du dépôt (aperçu)

| Élément | Rôle |
|---------|------|
| `pages/` | Écrans (tableaux de bord, fiche ticket, login). |
| `actions/` | Traitement des formulaires (connexion, mise à jour ticket, etc.). |
| `includes/` | Connexion BDD, fonctions communes, en-tête / pied de page. |
| `database/` | Scripts SQL (schéma). |
| `docs/` | Documentation projet et thèse. |
| `src/assets/` | Feuilles de style, images, bibliothèques front (Bootstrap, ApexCharts, etc.). |

---

## Crédits gabarit UI

L’interface s’appuie sur des ressources de type **admin dashboard Bootstrap** (thème MaterialM / NiceAdmin). Le dépôt peut contenir des fichiers issus du gabarit d’origine ; l’**implémentation métier GIA** (PHP, règles, SQL) est spécifique au projet.

---

## Licence

Le code métier et la documentation du projet relèvent des conditions fixées par l’équipe / l’établissement (PFE). Les composants tiers restent soumis à leurs licences respectives.
