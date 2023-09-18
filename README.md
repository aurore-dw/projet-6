# Projet 6 Formation Développeur d'application - PHP/Symfony Openclassrooms

Badge codacy : [![Codacy Badge](https://app.codacy.com/project/badge/Grade/5303a745e1244bf5a71385e2102359f6)](https://app.codacy.com/gh/aurore-dw/projet-6/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

## Guide d'installation :

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


