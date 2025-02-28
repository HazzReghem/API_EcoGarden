## Ecogarden - API Symfony 
API permettant la gestion de conseils de jardinage en fonction des mois, avec intégration de données méteorologiques via OpenWeather.

## Fonctionnalités :
- Gestion des utilisateurs (CRUD, sécurité avec JWT)
- Gestion des conseils de jardinage par mois
- Intégration de l'API météo pour obtenir la météo d'une ville
- Système de rôles (`ROLE_USER`, `ROLE_ADMIN`)
- Mise en cache des données météo pour optimiser les performances

##  Prérequis
Avant d'installer le projet, assurez-vous d'avoir les éléments suivants :
- **PHP 8.1+**  
- **Composer**  
- **Symfony CLI** 
- **MySQL / PostgreSQL**

## Installation : 
1. Clonez le projet - Ouvrez le dans votre éditeur de code

2. Ouvrez le terminal - "cd Ecogarden" - "composer install" (afin d'installer les dépendances)

3. Créez un fichier ".env.local" (vous pouvez copier le .env présent et le renommer comme indiqué)

4. Configurez ce fichier comme ceci (en prenant soin de bien modifier pour indiquer vos informations) :

DATABASE_URL="mysql://user:password@127.0.0.1:3306/ecogarden"

JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem

JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem

JWT_PASSPHRASE="VotrePassPhrase"

OPENWEATHER_API_KEY="votre_clé_api"

6. Générez les clés JWT : 

"mkdir -p config/jwt

openssl genpkey -algorithm RSA -out config/jwt/private.pem

openssl pkey -in config/jwt/private.pem -pubout -out config/jwt/public.pem"

8. Créez la base de donnée et effectuez les migrations : 

"php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate"

10. Lancez le serveur : "Symfony serve"
## Les Routes de l'API : 

Authentification (JWT) :

_ POST /auth : Se connecter (obtenir un token JWT)

Gestion des utilisateurs :

_ POST /user : Créer un utilisateur (accessible sans authentification)

_ PUT /user/{id} : Modifier un utilisateur (ROLE_ADMIN)

_ DELETE /user/{id} : Supprimer un utilisateur (ROLE_ADMIN)

Gestion des conseils :

_ GET /conseil/{mois} : Obtenir les conseils d'un mois en particulier

_ GET /conseil : Obtenir les conseils du mois en cours

_ POST /conseil : Ajouter un conseil (ROLE_ADMIN)

_ PUT /conseil/{id} : Modifier un conseil (ROLE_ADMIN)

_ DELETE /conseil/{id} : Supprimer un conseil (ROLE_ADMIN)

Météo (via OpenWeather) :

_ GET /meteo/{city} : Obtenir la météo d’une ville
_ GET /meteo (Utilisateur connecté) : Obtenir la météo de la ville de l'utilisateur

## Sécurité et Authentification 

L'API utilise JWT pour l'authentification ! Les Routes sensibles ont un accès restreint grâce aux rôles attribués aux utilisateur (ROLE_USER & ROLE_ADMIN).
   
   
