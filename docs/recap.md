# Récap général de la saison

## Le Maker

### a) Pour l'installer

Faire la commande suivante dans le terminal`composer require maker`

### b) Comment l'utiliser

Dans le terminal, on utilise le préfixe make pour faire des commandes simplifiées pour créer des éléments

- Pour faire un Controller : `php bin/console make:controller`
- Pour faire une commande personnalisée : `php bin/console make:command`
- Pour faire un CRUD : `php bin/console make:crud`
- Pour faire une entité : `php bin/console make:entity`
- Pour faire des fixtures : `php bin/console make:fixtures`
- Pour faire un formulaire : `php bin/console make:form`
- Pour gérer les users : `php bin/console make:user`

## Les formulaires

### a) Installation

Pour installer les formulaires : `composer require symfony/form`

### b) Personnalisation du style des formulaires

On peut indiquer un format de style pour les formulaire dans `config/packages/twig.yaml`

Et ajouter par exemple la ligne : `form_themes: ['bootstrap_4_layout.html.twig']`

Ne pas ensuite oublier d'importer les fichiers CSS Bootstrap dans vos projets.

### c) Création d'un formulaire

- On commence par faire un `make:form`
- On indique ensuite le nom du formulaire, pas obligé d'indiquer "Type" dans le nom. Symfony le génère tout seul.
- On indique ensuite à quelle entité le form est relié
- Un raccourci existe : `make:form NomForm NomEntite` => va créer un formulaire `NomFormType` lié à l'entité `NomEntite`

- On crée une nouvelle instance de l'entité à traiter : `$entite = new NomEntite();`
- On peut ne pas associer d'entité au formulaire, dans ce cas là, on ne met pas d'argument au createForm ci-dessous. Et les données sont mises dans un tableau au lieu d'être injectées dans l'entité.

```php
  $form = $this->createForm(NomFormType::class, $entite, [
      // On désactive la validation HTML
      // Doc MDN : https://developer.mozilla.org/fr/docs/Web/Guide/HTML/Formulaires/Validation_donnees_formulaire#Exemple_dutilisation_de_lAPI_de_validation_des_contraintes
      'attr' => ['novalidate' => 'novalidate']
  ]);
```

- On demande ensuite à Symfony d'analyser la requête pour vérifier si le formulaire a été soumis et si les champs sont valides : `$form->handleRequest($request);`
  - Pour voir si c'est soumis : `$form->isSubmitted()`
  - Pour voir si c'est valide : `$form->isValid()`
  - Pour traiter les données du formulaire : 
    - On récupère le manager : `$manager = $this->getDoctrine()->getManager();`
    - On demande au manager de persister notre instance en cours : `$manager->persist($newCategory);`
    - On envoie les changement en BDD : `manager->flush();`

### d) Faire les validations de formulaire

#### Installation

Il faut installer le validateur : `composer require symfony/validator`
Et utiliser le use suivant : `use Symfony\Component\Validator\Constraints as Assert;`

#### Ajouter des validateurs en annotation des propriétés des entités

Par exemmple : 

```php
    /**  
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     */
    private $name;

```

On rajoute **donc** une ligne pour chaque validation à faire au sein des annotations de la propriété de l'entité

- Si au moment de la soumission du formulaire, une ou plusieurs validations ne passe pas, Symfony va rajouter un message d'erreur sur le champ fautif

### e) Faire directement un CRUD

- Installer le package security : `composer require security-csrf`
- Lancer la commande de création de Crud : `php bin/console make:crud`
- Choisir l'entité concerné et ça fait tout automatiquement

Avantage :  ça va très vite

Inconvénient : C'est très basique niveau contenu et forme, il faut faire pas mal de modif si on veut quelque chose de précis

## Sécurité et User

### a) Configuration

- Il faut commencer par faire un `composer require security`

- Tout ce qui concerne la sécurité se trouve dans security.yaml

### b) Pour créer l'authentificateur

On commence par créer une entité User avec la commande : `php bin/console make:user` 

Rajouter éventuellement des proprirétés à cette entité User.

Mettre à jour la base de données pour prendre en compte la création/mise à jour de l'entité User
- `php bin/console doctrine:schema:update --force`

Pour créer le formulaire d'authentification
- Faire `php bin/console make:auth`
- Choisir 1 pour login
- Appeler la classe `AppAuthenticator`
- Appeler le controlleur `SecurityController`
- Pour autoriser l'accès au logout à tout le monde, dans `security.yaml`, dans la partie access_control, rajouter la ligne : `- { path: ^/login$, roles: IS_AUTHENTICATED_ANONYMOUSLY }`

### c) Pour générer le hash d'un password pour le rentrer à la main dans la BDD

`php bin/console security:encode-password`

### d) Indiquer la route une fois le login fait

Sur AppAuthenticator, il faut juste indiquer le nom de la route sur laquelle rediriger un user qui se connecte sur dans la méthode `onAuthenticationSuccess` et supprimer la ligne qui fait un throw d'une erreur

```php
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
      {
          if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
              return new RedirectResponse($targetPath);
          }

          // Tout s'est bien passé, on redirige vers la page d'accueil
          return new RedirectResponse($this->urlGenerator->generate('main_home'));
          // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
      }
```

### e) Récupérer le user 

- Depuis un controller, faire un `$this->getUser();`
- Depuis un template twig, faire un `{{ app.user }}`


### f) Faire le CRUD d'un user

#### En passant par make:crud

La commande est simple : `make:crud User`

- Si en BDD on a un tableau pour les roles, il peut y avoir une erreur sur le formulaire d'ajout d'un user
- Pour le corriger il faut, sur `UserType.php`, faire les modifications ci-dessous
  - Définir le `ChoiceType::class`
  - Définir le tableau des choix possibles `'choices' => []`
  - Définir l'option pour pouvoir choisir plusieurs valeurs `'multiple' => true`
  - Définir l'option pour les afficher sous forme de checkbox `'expand' => true`

- Par défaut la confidentialité des mots de passe n'est pas configuré, il faut modifier ça :
  - Dans `UserType`, il faut changer la configuration de `password` : `->add('password', PasswordType::class)`, çe met un champ password dans le form
  - Puis dans le `UserController`, il faut lui dire de hasher le password (Dans `new` et `edit`)
    - On récupère le password dans le user : `$password = $user->getPassword();`
    - On hashe le password : 
      - Ajouter le use : `use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;`
      - Ajouter `UserPasswordEncoderInterface $passwordEncoder` en injection de dépendance au controller
      - Rajouter dans le controller `$encodedPassword = $passwordEncoder->encodePassword($user, $password);`
    - On set le nouveau mot de passe dans le user : `$user->setPassword($encodedPassword);`
    - On peut aussi tout regrouper dans une seule instruction :
```php
    $user->setPassword(
                $passwordEncoder->encodePassword($user, $user->getPassword())
            );
```

- Si on veut mettre deux champs password pour le confirmer, sur UserType : 
  - On met le type `RepeatedType` sur le add password
  - On rajoute une option pour indiquer que ce sont des champs de password : `'type' => PasswordType::class,`
  - On indique le label du premier champ : `'first_options'  => ['label' => 'Mot de passe'],`
  - On indique le label du deuxième champ : `'second_options' => ['label' => 'Veuillez répétez le mot de passe'],`
  - On personnalise le message d'erreur : `'invalid_message' => 'Les deux mots de passe doivent être identiques',`

### e) Les roles des users

- Par defaut, tous les users ont le `ROLE_USER`, c'est défini ainsi dans la méthode `getRoles` de l'entité `User.php`
- On peut créer les roles que l'on veut. La seule règle de bonne pratique, c'est de tout écrire en majuscule avec en préfixe `ROLE_`

#### Définir les roles dans security.yaml

- Les roles se définissent dans `security.yaml`
- Dans la partie `access_control`
- Créer une ligne par règle d'accès :
  - Pour que tout le monde ait accès au login : `{ path: ^/login$, roles: IS_AUTHENTICATED_ANONYMOUSLY }`
  - Exemple accès aux pages de séries pour les users : `{ path: ^/show/list, roles: ROLE_USER }`
  - Exemple accès aux pages user pour les admins `{ path: ^/user, roles: ROLE_ADMIN }`
  - Exemple d'accès sur des méthodes `{ path: ^/, methods: ["DELETE"], roles: ROLE_ADMIN }`

- Il est possible de hiérarchiser les roles
- Dans `security.yaml` toujours, rajouter après le bloc `acces_control`, un bloc `role_hierarchy`
- On peut définir des hiérarchies de user
  - Par exemple : `ROLE_ADMIN: ROLE_USER` , ici ROLE_ADMIN est au dessus de ROLE_USER, il hérite donc de tous ses accès

#### Définir des roles directement dans les controllers

Dans certains cas, on ne voudra pas restreindre les accès dans security.yaml via une route mais directement sur un controller.

Ca peut être utile, si on veut ajouter des conditions sur les accès, par exemple, on peut supprimer une série, sauf s'il a été créé il y a moins de trois jours, il faut être admin pour le supprimer

Pour indiquer ça sur le controller : 
- Pour vérifier un role : `$hasAccess = $this->isGranted('ROLE_ADMIN');`
- Pour vérifier un role + restreindre l'accès : `$this->denyAccessUnlessGranted('ROLE_ADMIN');`

- Cependant, le code peut vite devenir lourd dans les controllers

On passe par des voters :
- Pour créer des voters : `php bin/console make:voter`
- Lui donner un nom.
- Et le reste de la configuration se fait dans le fichier créé dans `Security/Voter` . Voir exemple dans le projet O'flix
- On va créer les logiques d'accès dans la fonction voteOnAttribute

Le voter est appelé grâce à le denyAccessOrGranted dans le controller. Il va aller chercher s'il y a un voter qui existe pour l'entité en question

Dans le voter, il y a deux étapes :
- En premier lieu, la méthode supports regarde si ce qu'on doit tester est bien présent dans la liste, sinon le voter répond directement false
- Si c'est dans la liste, on passe à la deuxième étape , le voteOnAttribute
- Dans ce voteOnAttribute : 
  - On indique les logiques d'autorisation pour chacun des cas à tester
  - Pour chaque cas, on retourne un true or false selon les conditions que l'on veut accéder

#### Indiquer des roles dans les annotations

On peut indiquer un role admin sur un controller directement depuis les annotations avec la ligne : 

`@IsGranted("ROLE_ADMIN")` , par exemple

En rajoutant le use nécessaire : `use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;`

Doc : https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html#annotations-for-controllers

#### Récupérer les roles dans twig

Avec la fonction `{{ is_granted('ROLE_USER') }}`, et on modifie le type de rôle selon nos besoins. Répond un booléen pour pouvoir faire des conditions d'affichage

## Les services

Les services sont des classes que l'ont peut poser n'importe où dans notre projet et qui interagissent avec notre projet. Elles ont un role précis. Ca sert à limiter le code dans les controllers.

### a) Créer un service

- On place généralement les services dans **src/Service** (Dossier à créer)
- On crée un fichier par service
- Dans ce fichier on crée une classe classique qui effectue la tâche voulue

On peut ensuite importer nos services:
    - Dans les controllers : En injection de dépendance directement dans la méthode du controller ou depuis le constructeur
    - Dans tout autre classe (si c'est possible) : dans le constructeur uniquement

## Les commandes personnalisées

On peut créer des commandes personnalisées dans Symfony en faisant `php bin/console make:command`

Cela va créer : 
- un dossier `src/Command` s'il n'existe pas encore
- Et un fichier correspondant à notre commande

Faire une commande permet de se faire des raccourcis sur des tâches à effectuer.

:warning: Pour utiliser une commande avec un constructeur, il faut penser à initialiser le constructeur de la classe parente `Command` avec : 
```php
parent::__construct();
```

Ensuite,on peut directement executer le code à l'intérieur de cette classe avec une commande dans le terminal :
```bash
   php bin/console app:ma:commande:custom
```

## Les événements

Les évenements concernent trois parties différentes de Symfony : 
- Le kernel : Pour tout ce qui concerne les controlleurs (Request, Response, ...)
- Doctrine : Pour tout ce qui concerne les entités en BDD
    - 2 types :
      - Les lifeCycle Callbacks qu'on crée directement sur une entité
      - Les subscribers qui sont des classes à part entière
      
- Form : Pour tout ce qui concerne les formulaires


Pour créer un event subscriber

`make:subscriber`
Choisir une classe name : `Kernel`

Dans l'exemple, on a vu l'event : `kernel.request`

Le fichier de subscriber se créer dans le dossier `src/EventSubscriber`

Un subscriber va s'inscrire à un évenement particulier et va déclencher une fonction quand cet évenement se produit.

L'évenement se produit quelque soit la route si on ne lui précise rien 

On a aussi des events listener qui fonctionne à peu près pareil sauf qu'il y a une config en amont à faire en dehors de la classe dans le **services.yaml** alors que pour le subscriber, toute la configuration se fait à l'intérieur de la classe

- Il y a aussi des evenements liés au formulaire

C'est ceux qui sont le plus utilisés, décomposées en 5 événements
- PreSetData
- PostSetData
- PreSubmit
- Submit
- PostSubmit

On peut par exemple modifier un formulaire en fonction de son contexte

- Sur les événements de dom (Formulaire par exemple), on peut appeller la méthode ->addEventListener
```php
// On va se brancher à un évenement du cycle de vie de formulaire CharacterType
// On va écouter l'évènement qui est propagé juste avant la soumission
$builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event){
    $form = $event->getForm();
    $submitedData = $event->getData();

    // Une fois l'évènement intercepté, on va pouvoir faire des vérifications sur l'utilisateur
    // On met en place une logique métier spécifique à notre projet
    dump($form, $submitedData);
});
```

Documentation Pour aller plus loin : 

- https://symfony.com/doc/current/components/http_kernel.html
- https://symfony.com/doc/current/reference/events.html
- https://symfony.com/doc/current/form/events.html
- https://symfony.com/doc/current/doctrine/events.html

## Les API

Pour échanger des informations avec des services externes ou avec une partie front d'un projet qui est fait en react par exemple, on utilise une API. Les controllers renvoient du contenu Json au lieu d'appeler des vues. Le Json va être récupéré en Front par exemple pour créer le html avec le contenu.

### a) Les bonnes pratiques

On essaye de respecter au mieux possible les bonnes pratiques de l'API REST : 
- https://medium.com/@mwaysolutions/10-best-practices-for-better-restful-api-cbe81b06f291
- https://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api

- Utiliser des noms et pas des verbes dans les routes, par exemple, on met un /shows ou /shows/list pour récupérer toutes les séries. Et /shows/id pour récupérer une série.
- En GET, on ne modifie rien sur les entités, on s'en sert juste pour récupérer de l'information. Pour "modifier" quelque chose, on utilise POST, PUT, PATCH, DELETE.
- Utiliser les entêtes HTTP qui correspondent exactement au format retourné (Content-type et Accept)
- Utiliser les bons codes HTTP : 200 pour les succès (200 OK, 201 Created, 202 Accepted, 204 No content (Quand on supprime une info avec succès) ), 300 pour les redirections, 400 pour les erreurs du client web (400 Bad request, 404 Unhaorized (Il faut se connecter pour accéder à la ressource), 403 Forbidden (Le user n'a pas accès au contenu), 404 Not Found, 405 Method not allowed ), 500 pour les erreurs du serveur (500 Internal servor error)

### b) Créer son API

- Faire un nouveau controller qu'on place dans un sous dossier API (si plusieurs controlleurs, sinon un fichier ApiController.php si quelques méthodes) et dans un sous dossier avec le numéro de version
- Mettre l'option --no-template pour indiquer qu'on ne veut pas de rendu pour se controller -> `php bin/console make:controller --no-template`
- Si on est pas sur un projet symfony en --full, faire un `composer require serializer`

>> Voir un exemple avec [ApiController](https://github.com/O-clock-Isengard/symfony-oflix/blob/master/src/Controller/ApiController.php)

- Pour corriger les éventuelles erreurs de circular reference, il faut paramétrer le serializer
  - Sur les propriéts des entités que l'on veut afficher, avec l'annotation `@Groups("groupName")` en faisant un `use Symfony\Component\Serializer\Annotation\Groups;` sur l'entité
  
  **Dans l'entité**
    ```php
    use Symfony\Component\Serializer\Annotation\Groups;
    // ...
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * 
     * @Groups({"show_list"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Vous devez saisir un nom de série")
     * 
     * @Groups({"show_list"})
     */
    private $title;
  ```
  
  **Dans le controleur**
  ```php
    return $this->json($shows, 200, [], [
        'groups' => 'show_list'
    ]);
  ``` 
- Envoi une réponse en JSON pour une création / modification:
  ```php
        return $this->json([
            'success' => $success,
            'message' => $message,
            'errors' => $errors
        ]);
  ```

Il y a souvent besoin de faire des `php bin/console cache:clear` quand on veut tester des modifs

### c) API Platform

On a bien sûr des outils pour aller plus vite : https://api-platform.com/

- on installe l'api `composer require api `
- Ajouter l'annotation @ApiResource sur toutes les entités à utiliser avec l'api

On ne peut pas utiliser Api platforme sur l'apothéose. C'est trop simplifié pour le TP.

Si vous rencontrez des bugs liés au CORS, vous pouvez utiliser le Bundle Nelmio, qui vous configurera l'application pour accepter les CORS :
- `composer req cors`

Pour en savoir plus : https://github.com/nelmio/NelmioCorsBundle
