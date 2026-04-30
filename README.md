# 💻 Système de Gestion de Stock - Magasin Informatique

Application web de gestion de stock pour un magasin informatique, développée en PHP et MySQL.

## Fonctionnalités

- Authentification sécurisée avec bcrypt
- Gestion des produits: ajouter, modifier, supprimer
- Gestion des catégories
- Gestion du stock
- Paramètres utilisateur: email, mot de passe, seuil d'alerte
- Notifications email via PHPMailer
- Interface responsive

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- XAMPP ou équivalent
- Composer

## Installation

### 1. Installer Composer

Si Composer n'est pas installé sur votre PC:

1. Télécharger l'installeur: **[https://getcomposer.org/Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)**
2. Double-cliquer sur le fichier `.exe`
3. Suivre l'assistant d'installation
4. Sélectionner le PHP de XAMPP: `C:\xampp\php\php.exe`
5. Terminer l'installation

### 2. Installer les dépendances PHP

Dans PowerShell, aller au dossier du projet:

```powershell
cd C:\xampp\htdocs\projet_web
composer install
```

### 3. Configurer la base de données

- Ouvrir phpMyAdmin: http://localhost/phpmyadmin
- Créer une nouvelle base de données: `magasin_informatique`
- Importer le fichier: `config/base_donnees.sql`
  - Aller dans l'onglet "Importer"
  - Sélectionner le fichier SQL
  - Cliquer sur "Exécuter"

### 4. Configurer SMTP (pour les emails)

- Ouvrir l'application: http://localhost/projet_web/
- Se connecter avec: Email `admin@magasin.com` / Mot de passe `Admin@123`
- Aller dans **Paramètres** → **Configurer SMTP**
- Remplir les données SMTP (exemple: Gmail, Mailtrap, etc.)

## Structure du projet

```
projet_web/
├── index.php
├── login.php
├── logout.php
├── README.md
├── composer.json
├── config/
│   ├── base_donnees.sql
│   └── connexion.php
├── css/
│   └── style.css
├── images/
│   └── produits/
├── includes/
│   ├── auth.php
│   ├── email_service.php
│   ├── fonctions_categories.php
│   └── fonctions_produits.php
└── pages/
   ├── ajouter_categorie.php
   ├── ajouter_produit.php
   ├── modifier_produit.php
   ├── parametres.php
   ├── stock.php
   └── supprimer_produit.php
```

## Base de données

Le fichier `config/base_donnees.sql` contient le schéma et les données initiales:

- 11 catégories de produits informatiques
- 110 produits de départ, 10 par catégorie
- Des URLs d'images publiques dans le champ `photo`

Le fichier `config/connexion.php` gère la connexion MySQL et l'initialisation automatique du compte admin si nécessaire.

## Configuration

Si besoin, adaptez la connexion dans `config/connexion.php`:

```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'magasin_informatique';
```

## Dépendances

- PHPMailer 7.x


## Licence

Projet académique.
