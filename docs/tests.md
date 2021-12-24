# Tests et Qualité

- [Documentation symfony](https://symfony.com/doc/current/testing.html)

## Pourquoi Tester

Quand on commence à travailler à plusieurs sur un projet (Apothéose, ...), on augmente le risque de voir notre application embarquer des bugs.

Pour donc assurer la qualité de notre code, nous allons mettre en place des Tests.

Il en existe 3 types : 

- Tests unitaires : on vérifie qu'une ou plusieurs classes fonctionnent comme prévu
- Tests fonctionnels : on vérifie qu'une ou plusieurs pages web fonctionnent comme prévu
- Smoking testing : Un test fonctionnel en mode accéléré. On test rapidement que chaque page principale fonctionne. Sinon (car il n'y pas de fumée sans feu), on a un bug qui se cache quelque part, beaucoup profond que ce que l'on pense.

## Installation de PHP Unit

```bash
    composer require --dev symfony/phpunit-bridge
```

On lance ensuite un premier test, qui va installer des packages supplémentaires :
```bash
    php bin/phpunit

    # ...Installation de packages....
```

## Un service sans dépendance dans le controller
On vérifie qu'une classe (Service, un utilitaire, ...) fonctionne comme prévu. Pour ce faire :
- On va créer une class dans le dossier `tests` suffixée du mot-clé "test" et qui étends `TestCase`
- On implémente une méthode dont le nom commence par `test`, et dans celle-ci on met à la logique de test
- On lance la commande `php bin/phpunit --debug`

**tests/Service/MonServiceTest.php**
```php
namespace App\Tests\Service;
use PHPUnit\Framework\TestCase;
use App\Service\MonService;

class MonServiceTest extends TestCase {
    public function testMaMethode() {
        // On appelle le service
        $service = new MonService();

        // On récupère un résultat du service
        $result = $service->uneMethode();

        // On vérifie que le service fait exactement ce pourquoi il a créé.
        // En partant d'un résultat attendu, on vérifier si celui-ci est égal à ce que nous retourne le service
        $this->assertEquals('cequeJattend', $result, "Le service ne fonctionne pas comme prévu : $result est différent de cequeJattend");
    }
}
```

Il ne nous reste plus qu'à tester notre service en ligne de commande :
```bash
    php bin/phpunit --debug
```

Si le resultat est "OK" en vert, alors tout s'est passé. Notre service est conforme à nos attentes.

## Un service avec dépendance dans le controller (RandomQuote)

Pour instancier un service depuis une classe de Test, on ne peut directment le faire depuis le construteur de la classe (injection de dépendance). A la place, on peut passer le container de service symfony (Gestionnaire de service) que l'on appelle grace au Kernel symfony.

```php
    use App\Kernel;
    // ...

    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $kernel->boot();

    // On récupère le container de service (Donc c'est l'annuaire qui contient le nom de tous services)
    $container = $kernel->getContainer();

    // On peut désormais récuperer le service RandomQuote
    /** @var MonService */
    $monservice = $container->get(MonService::class); // MonService::class ==> fqcn
```

Une fois que l'on dispose du service, il ne reste plus qu'à le tester en utilisant l'une des méthodes d'assertions de PHP-unit : 
- [Assertions PHP-Unit](https://phpunit.readthedocs.io/fr/latest/assertions.html)
