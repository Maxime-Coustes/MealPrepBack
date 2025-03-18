# MealPrepBack

## Description
MealPrepBack est un backend pour l'application de planification de repas, permettant de gérer les ingrédients, les recettes, les profils utilisateurs et les calculs nutritionnels. Ce projet est basé sur Symfony et API Platform.

## Prérequis

Avant de commencer, vous devez avoir installé les éléments suivants sur votre machine :

- PHP 8.2+
- Composer
- Symfony CLI
- Une base de données configurée (MySQL, PostgreSQL...)

## Installation

1. Clonez le dépôt du projet sur votre machine locale :

```bash
git clone git@github.com:Maxime-Coustes/MealPrepBack.git

2. Accéder au dossier du projet
cd MealPrepBack

3. Installer les dépendances
composer install

4. Configuration de l'environnement
cp .env .env.local

Modifiez .env.local avec vos informations de base de données:
DATABASE_URL="mysql://username:password@127.0.0.1:3306/mealprepdb?serverVersion=5.7"

5. Exécuter les migrations
php bin/console doctrine:migrations:migrate

6. Démarrer le serveur
symfony serve

L'API sera accessible à l'adresse : http://127.0.0.1:8000.


Utilisation de l'API
Endpoint : /ping
Méthode : GET

Permet de vérifier que l'API fonctionne.

Réponse :

{
  "status": "success",
  "message": "API is running"
}

Authentification
L'API utilise JSON Web Tokens (JWT) pour l'authentification des utilisateurs. Pour obtenir un token, envoyez une requête POST à /api/login avec les informations d'identification (par exemple, email et mot de passe).

Autres endpoints
Les autres endpoints de l'API (comme ceux pour gérer les ingrédients, produits, recettes, etc.) seront ajoutés et documentés au fur et à mesure de l'avancement du projet.