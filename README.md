# O'Flix

## 1/ Infos sur le projet

Projet développé avec Symfony 5 / Twig / Bootstrap / JS sous **PHP v8**

Une fois cloné => `composer install` => création du fichier **env.local** => y ajouter le DATABASE_URL
`DATABASE_URL=mysql://username:password@127.0.0.1:3306/oflix?serverVersion=10.4.21-mariadb` <= ***vérifier la version mariadb***

Le dossier **DOCS** contient:
   - Le MCD du projet.
   - contient également des versions de Controller qui n'utilisent pas les Forms pour CRUD les données.
   - le README du prof avec des infos sur les procédures/étapes pour monter le projet.

## 2/ Les différentes commandes utilisées pendant le projet

Si Symfony Cli installé, il est recommandé d'utiliser `symfony console ...` à la place de `php bin/console ...`

- *Création de la base du projet*
   - `composer create-project symfony/skeleton my-project-name`  
   ou
   - `symfony new nom-du_projet` => symfony skeleton
   - `symfony new nom-du-projet --full` => symfony website  
**nom du projet = pas d'espace, ni apostrophe, ...**

- *Ajout des dépendances nécessaires*
  - `composer require annotations maker profiler debug twig asset var-dumper orm validate`

- *Création des controlleurs*
   - `php bin/console make:controller`

- *L'inspecteur de route*
   - `php bin/console debug:router`

- *Creation des Entités*
   - `php bin/console make:entity`

- *La BDD via Doctrine (ORM)*
   - Création: `php bin/console doctrine:database:create` le fichier env.local doit être fait avant
   - Mise à jour: 
      - Pour vérifier avant `php bin/console doctrine:schema:update --dump-sql`
      - Pour appliquer la mise à jour `php bin/console doctrine:schema:update --force`

- *Création des relations entre les entités*
   - `php bin/console make:entity`, appeler la première entité de la relation, puis la deuxième et suivre les étapes
   - finir par une maj de la BDD avec la commande `schema:update`

- *Les DATAfixtures* 
   - Ensemble de données/tests qui va pré-remplir la BDD avec des données proches du projet final
   - `php bin/console:fixtures:local` **ATTENTION!** efface toutes les données dans la BDD
   - `php bin/console:fixtures:local --append` ajoute les données en plus de celle présentes en BDD
   - Utilsation de Fake pour remplir la BDD par de fausses données
      - `composer require fzaninotto/faker`

- *Pour créer très rapidement une relation entre deux entités et les différentes actions + templates*
   - `composer require security/csrf`
   - `php bin/console make:crud`

- *Création des formulaires Symfony*
   - `php bin/console make:form`  
   Pour donner du style aux formulaires via twig et bootstrap, ajouter dans *config/packages/twig.yaml*  
`form_themes: ['bootstrap_5_layout.html.twig']`

## 3/ Les différentes étapes de la sécurité

- *Création d'un back office à l'aide des commandes*  
  - `composer require easycorp/easyadmin-bundle`
  - Créer à la main le dossier Admin dans le dossier Controller avant la prochaine commande
  - `php bin/console make:admin:dashboard` => installe l'interface/route admin
  - Pour rajouter des liens, décommenter le "yield" dans DashboardController
  - `php bin/console make:admin:crud`=> installe les différentes commandes CRUD

- *L'authentification - authentication = login*
   - `composer require symfony/security-bundle`
   - Dans le fichier *config/packages/security.yaml* on retrouve les conditions d'accès d'authentification
   - Création de l'entity/repository User :  
   `php bin/console make:user`
   - Pour créer un formulaire et les règles de connexion :  
   `php bin/console make:auth`
   - Pour générer un mot de passe hash à entrer manuellement dans la BDD :  
    `php bin/console security:hash monmotdepasse`  
   *Note: **Salt** => rajoute une couche de sécurité aux MDP hashés, c'est à nous de définir le mot, le clef à ajouter*

- *Autorisation - Voters*
   - Plusieurs méthodes avec les rôles:
      - En annotation dans le Controller: `
      ```
      /**
      * @IsGranted("ROLE_ADMIN")
      */
      ```
      - Directement dans la méthode du Controller:  
      `$this->denyAccessUnlessGranted('ROLE_ADMIN');`
      - dans le fichier *config/packages/security.yaml*:  
      En définissant l'accès réservé par rapoort à la route  
       `access_control: - { path: ^/admin, roles: ROLE_ADMIN }`  
      Dans le même fichier on peut configurer une hiérarchie des rôles:  
      ```  
         role_hierarchy:
            ROLE_ADMIN:       ROLE_USER
            ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_USER] 
      ```
   - Les Voters:
      - Ils permettent de donner des droits spécifiques pour certaines actions qui seraient de base réservées un autre type de User, ils ne modifient le rôle. Exemple, l'édition ou la suppression d'un post serait de base réservé à un rôle ADMIN mais il peut être également attribué à l'auteur de ces post, c'est là qu'intervienne les Voters.  
      C'est une Class qui a pour rôle de décider si on a le droit de faire des opétations. Il répondra toujours par 3 choix: oui, non, je ne sais pas.
      Peut être utiliser directmeent dans un controller (voir doc) ou en créant un Voter dans *src/Security/MonVoter.php*. Cette création peut être faite à la main ou avec un `make:voter`.  
      Dans le Voter, il y a deux étapes, une vérification de l'existence des droits et l'action d'autorisation. (Voir le UserVoter)  
      Une fois établi dans le Voter on vient appliquer les droits sur la route dans le Controller de deux manières possibles:

      1) **Dans l'annotation:**

         ```
         /**
         * On donne un attribut et un subject
         * @IsGranted("user_show", subject="user") 
         */
         ```

      2) **Dans la méthode:**
      `$this->denyAccessUnlessGranted('user_show', $user);`

      - Dans le Voter, on peut ajouter un rôle en plus de la condition, en ajoutant un constructor:

         ```
         private $security;

         public function __construct(Security $security)
         {
            $this->security = $security;
         }
         ```
         et en ajoutant une condition avant ou dans le switch (voir UserVoter)

## 4/  Symfony <=> API

### Se connecter à une API:

Nécessite `composer require symfony/http-client`  
exemple avec l'api de OMDB et une command custom dans *src/Command/ShowRatingCommand.php*

## 5/  Symfony == API

### a/ Le JSON format 

1) Créer un controller pour les API (ici *ApiController.php*)
2) Créer des routes   
ex : `api/show/list`, `api/show/{id}/detail`, `api/show/new`, ...
3) Installer le logiciel **Insomnia** avec lequel on va pouvoir tester ces routes
4) https://symfony.com/doc/current/components/serializer.html  
Pour transformer un objet en JSON, on "normalize" l'objet qui devient un tableau et on "encode" ce tableau pour le convertir en JSON. Inversement pour passer d'un Json à un Objet, on "decode" pour avoir un tableau, puis on "deserialize" pour obtenir l'objet.  
Nativement, avec la fonction `json()`, Symfony sait encoder et decoder  tableau <=> json, mais il ne sait pas normaliser et dénormaliser un objet <=> un tableau.   
Il est nécessaire d'installer le composant *serializer*  
`composer install symfony/serializer`
5) Tester les routes, si une erreur "Circular Reference" survient, c'est que le composant va tourner en boucle sur une requête qui contient l'objet de la recherche.  
Il faut aller utiliser les *@Groups* dans les annotations pour lui indiquer quels sont les champs concernés par la requête.  
https://symfony.com/doc/current/components/serializer.html#attributes-groups

### b/ La Sécurité de l'API:

Il est important d'empêcher n'importe quel utilisateur de pouvoir utiliser ces routes, pour cela on va user d'un authentificateur, le JWT.  
***JWT -> JSON Web Token***  
Il est composé  3 parties qui seront hashées:  
   - *xxxxxx.yyyyyyyy.zzzzzzz*  
      *Header.Payload.Signature*

La partie "Signature" sera notre Passphrase.  
L'utilisation de ce JWT, nécessite l'installation d'un bundle spécifique, le LexikJWTAuthenticationBundle

https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.md#installation  

1) `composer require "lexik/jwt-authentication-bundle"`
2) Générer les SSH keys:
   - créer un dossier *config/jwt*
   - entrer la commande  
   `php bin/console lexik:jwt:generate-keypair`  
   Si erreur, utilisez les anciennes commandes pour générer cahcune des clefs
   ```
   mkdir -p config/jwt
   openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
   openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
   ```
   - dans le fichier *.env*, déplacer le Passphrase dans *.env.local* et inclure notre passphrase personnelle
   - ajouter les firewalls dans *config/packages/security.yaml* => voir la doc  
   ATTENTION à la version de Symfony utilisée
3) Créer la route `api_login_check` dans un Controller (ici dans SecurityController)
4) Tester la route avec Insomnia, un token sera envoyé en réponse
5) Récupérer ce token et tester une autre route API comme `/api/show/list` en mettant le Token dans le Bearer de la requête. Si le token est valide, on aura la liste des Show (sur la route `api/show/list`), sinon une erreur sera envoyé avec le message "Token invalid" ou "Expired JWT Token".