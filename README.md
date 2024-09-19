# P07_BileMo
### Adaptations spécifiques de l'API pour la société BileMo :

1. **Cloner le dépôt :**
   git clone git@github.com:LudGold/P07_BileMo.git

2. **Configuration :**
  composer install
   Configuration : version min required to run this project :

  PHP 8.2.0 PHPMyAdmin 5.2.0 MySQL 8.0.31 - Port 3306 Composer
  Database : You need the following datas to match the database configuration : create .env.local file on project's root directory, copy and paste into it the content of .env file configurate the field database_url settings 
  Then execute the following commands :

  php bin/console d:d:c
  php bin/console d:m:m
  php bin/console d:f:l

3. **Documentation API :**
   
   La documentation faite avec Nelmio sera accessible soit par la home page : https://127.0.0.1:8000 et un lien à cliquer
   soit directement à l'adresse suivante:
   https://127.0.0.1:8000/api/doc

4. **Tester l'API :**
   With Postman or the Nelmio documentation


