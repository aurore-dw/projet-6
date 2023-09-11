#Projet 6 Formation Développeur d'application - PHP/Symfony Openclassrooms

Badge codacy : [![Codacy Badge](https://app.codacy.com/project/badge/Grade/5303a745e1244bf5a71385e2102359f6)](https://app.codacy.com/gh/aurore-dw/projet-6/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

##Guide d'installation :

- Clonez ou télécharger le repository GitHub
- Configurez vos variables d'environnement tel que la connexion à la base de données dans le fichier .env
- Téléchargez et installez les dépendances back-end du projet avec Composer : composer instal
- Créer un build d'assets grâce à Webpack Encore avec yarn : yarn run build
- Mettre en place la base de donnée : php bin/console doctrine:database:create et 
                                      php bin/console doctrine:migrations:migrate
- Implémenter les fixturer : php bin/console doctrine:fixtures:load
