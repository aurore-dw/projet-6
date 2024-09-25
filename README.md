# Projet 6 Formation Développeur d'application - PHP/Symfony Openclassrooms

Badge codacy : [![Codacy Badge](https://app.codacy.com/project/badge/Grade/5303a745e1244bf5a71385e2102359f6)](https://app.codacy.com/gh/aurore-dw/projet-6/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

## Contexte du projet :

Jimmy Sweat est un entrepreneur ambitieux passionné de snowboard. Son objectif est la création d'un site collaboratif pour faire connaître ce sport auprès du grand public et aider à l'apprentissage des figures (tricks).

Il souhaite capitaliser sur du contenu apporté par les internautes afin de développer un contenu riche et suscitant l’intérêt des utilisateurs du site. Par la suite, Jimmy souhaite développer un business de mise en relation avec les marques de snowboard grâce au trafic que le contenu aura généré.

Pour ce projet, nous allons nous concentrer sur la création technique du site pour Jimmy.

## Description du besoin :

Vous êtes chargé de développer le site répondant aux besoins de Jimmy. Vous devez ainsi implémenter les fonctionnalités suivantes : 

- Un annuaire des figures de snowboard. Vous pouvez vous inspirer de la liste des figures sur Wikipédia. Contentez-vous d'intégrer 10 figures, le reste sera saisi par les internautes ;
- La gestion des figures (création, modification, consultation) ;
- Un espace de discussion commun à toutes les figures.

Pour implémenter ces fonctionnalités, vous devez créer les pages suivantes :

- La page d’accueil où figurera la liste des figures ; 
- La page de création d'une nouvelle figure ;
- La page de modification d'une figure ;
- La page de présentation d’une figure (contenant l’espace de discussion commun autour d’une figure).

## Guide d'installation du projet :

1. Clonez ou télécharger le repository GitHub

- `git clone https://github.com/aurore-dw/projet-6.git`

2. Configurez vos variables d'environnement tel que la connexion à la base de données dans le fichier .env
  
3. Dans le projet, téléchargez et installez les dépendances back-end
   
- `composer install`
- `yarn install`

4. Créer un build d'assets grâce à Webpack Encore avec yarn
   
- `yarn run build`

5. Mettre en place la base de donnée
   
- `php bin/console doctrine:database:create`
- `php bin/console doctrine:migrations:migrate`
- `php bin/console doctrine:schema:update --force`

6. Implémenter les fixtures
   
- `php bin/console doctrine:fixtures:load`

7. Démarrer le serveur web local de Symfony
   
- `symfony server:start`

8. Lancer Webpack encore
   
- `yarn run watch`

10. Accéder à l'application, généralement `http://localhost:8000`


