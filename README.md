# Eleksyon | Application de Vote en Ligne

**ATTENTION: le développement est encore en cours de finalisation**

## Description
Une application web sécurisée et intuitive pour organiser des votes en ligne. Elle permet aux utilisateurs de créer, gérer et participer à des scrutins électroniques de manière simple et transparente. Conçue pour garantir la confidentialité et l'intégrité des votes, cette application a été pensée pour les unités de la Gendarmerie Naionale en premier lieu, mais pourra être facilement adaptée aux besoins d'autres administrations souhaitant procéder à des élections de manière dématérialisée.

## Fonctionnalités
- Création de scrutins avec options personnalisables, telles que la limitation du "périmètre" electoral à un groupe d'utilisateurs ou à leurs unités
- Vote totalement anonyme et sécurisé, mais vérifiable par chaque participant
- Interface utilisateur utilisant le <abbr title="Système de Design de l'État">DSFR</abbr>&copy;, moderne et responsive, en cohérence avec les sites étatiques
- Résultats publiés de manière transparente
- Authentification des utilisateurs et insertion en BDD via SSO et LDAP pour éviter les votes multiples

## Prérequis
- PHP (version 7.2 ou supérieure) 
- Symfony&copy; et Composer
- Node.js et NPM
- MySQL&copy; (pour la base de données)
- Navigateur web moderne (cible: Firefox 128-esr, mais supporte très bien Chrome, Firefox, Safari, etc.)

## Installation
1. Clonez le dépôt :
   ```bash
   git clone https://github.com/loicdurand/eleksyon.git
   ```
2. Accédez au répertoire du projet :
   ```bash
   cd eleksyon
   ```
3. Installez les dépendances :
   ```bash
   npm install      # installe les dépendances (packages) NPM
   composer install # installe les dépendances (bundles) PHP
   ```
4. Configurez les variables d'environnement dans un fichier `.env` :
   ```env
    APP_ENV=dev

    LDAP_HOST=ldap://mon_ldap.domaine.fr
    LDAP_PORT=389
    LDAP_USER=admin 
    LDAP_PASSWORD=mot_de_passe
    BASE_DN='dc=exemple,dc=fr'

    COOKIE_NAME=nom_du_cookie
    COOKIE_DOMAIN=localhost
    PORTAL_URL='http://adresse_de_mon_sso/login'
    REST_URL='http://adresse_de_mon_sso/validate' 
    MAIL_URL='https://adresse_de_mon_sso/mail' # Pas encore implémenté
    
   ```
1. Lancez l'application :
   ```bash
   # PRÉPARATION DE LA BASE DE DONNÉES
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load

   # BUILD DES ASSETS
   php bin/console asset-map:compile
   npm run build

   # DÉMARRAGE DU SERVEUR
   npm run server
   ```
2. Accédez à l'application via `http://localhost:8000` dans votre navigateur.

## Utilisation
1. **Créer un scrutin** : ***Eleksyon*** n'utilise pas de compte administrateur. Lorsque vous vous connectez au site, vous êtes libre de créer un élection ou de participer aux scrutins ouverts par d'autres.
2. **Partager le scrutin** : Obtenez un lien à partager avec les participants, que vous pouvez envoyez aux différentes unités autorisées.
3. **Voter** : Les utilisateurs accèdent au lien, s'authentifient, et soumettent leur vote.
4. **Vérifiez l'intégrité de votre vote** Après avoir voté pour le candidat de votre choix, vous recevez une clé unique permettant - à vous seul - de vérifiez que votre vote n'a pas été altéré.
5. **Consulter les résultats** : Les résultats sont disponibles publiquement lorsqu'une élection est terminée.

## Technologies utilisées
- **Backend** : PHP, Symfony&copy;
- **Frontend** : TypeScript très léger
- **Base de données** : MySQL
- **Authentification** : Via SSO et LDAP
- **Hébergement** : Le site est hébergé sur serveur INTRANET local. Il n'est donc pas accessible au grand public et son accès est limité à l'administration pour laquelle il a été conçu.

## Contribution
Nous accueillons les contributions ! Pour participer :
1. Forkez le dépôt.
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/nouvelle-fonction`).
3. Commitez vos changements (`git commit -m "Ajout de nouvelle fonctionnalité"`).
4. Poussez votre branche (`git push origin feature/nouvelle-fonction`).
5. Ouvrez une Pull Request.

Veuillez lire notre [guide de contribution](CONTRIBUTING.md) pour plus de détails.

## Licence
S'agissant d'un projet conçu pour une administration française, et utilisant le <abbr title="Système de Design de l'État">DSFR</abbr>&copy;, celui-ci est [sous licence ouverte Etalab 2.0](https://www.etalab.gouv.fr/wp-content/uploads/2017/04/ETALAB-Licence-Ouverte-v2.0.pdf). Vous êtes libres de l'adapter à vos besoins si vous ne travaillez pas pour le compte d'une administration française, notament en retirant les éléments de design du <abbr title="Système de Design de l'État">DSFR</abbr>&copy;, ce qui est relativement aisé si vous avez l'habitude de'utiliser des bibliothèques telles Bootstrap&copy;, Materialize.css&copy;, voire Tailwind&copy;

## Contact
Pour toute question ou suggestion, ouvrez une issue sur GitHub.