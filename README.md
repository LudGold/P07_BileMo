# P07_BileMo
### Adaptations spécifiques de l'API pour la société BileMo :

1. **Cloner le dépôt :**
   git clone git@github.com:LudGold/P07_BileMo.git

2. **Configuration :**
  composer install
   Configuration : version min required to run this project :

     PHP 8.2.0 PHPMyAdmin 5.2.0 MySQL 8.0.31 - Port 3306 Composer
     Database : You need the following datas to match the database configuration : create ``.env.local`` file on project's root directory, copy and paste into it the content of ``.env`` file configurate the field 
    ``database_url ``  
     settings
   
     Then execute the following commands :

      * php bin/console d:d:c 
      * php bin/console d:m:m
      * php bin/console d:f:l

3. **Documentation API :**
   
  The documentation created with Nelmio will be accessible either through the home page: https://127.0.0.1:8000 by clicking on the "view documentation" link, or directly at the following address:
   https://127.0.0.1:8000/api/doc
   To access the various API routes, you must first obtain a JWT token by sending a POST request to the endpoint /api/login_check with the authentication details (username and password).

   The endpoint will return a JWT token that you need to include in the Authorization headers of your requests.


4. **Tester l'API :**
  
   * Postman: Create a collection of requests with the JWT token in the headers.
   * Nelmio: Navigate through the documentation, add the JWT token in the Authorization field (in the format Bearer <your_token>) before testing the protected routes.


