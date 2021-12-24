<?php

namespace App\Tests\Service;

use App\Kernel;
use App\Service\RandomQuote;
use PHPUnit\Framework\TestCase;

class RandomQuoteTest extends TestCase
{

    public function testQuote()
    {
        // Trop compliqué (voir impossible) de faire un new, car le service
        // randomQuote dépend d'autres services : on serait donc obliger de créer des instances
        // des services dont dépend RandomQuote.
        // $random = new RandomQuote();

        // Impossible d'utiliser le constructeur pour injecter notre service (voir service.yaml)
        // Solution: on va récuperer le service directement depuis le container service
        // pour appeller un service depuis le container, on doit rendre ce service "public"
        $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $kernel->boot();

        // On récupère le container de service (Donc c'est l'annuaire qui contient le nom de tous services)
        $container = $kernel->getContainer();

        // On peut désormais récuperer le service RandomQuote
        /** @var RandomQuote */
        $random = $container->get(RandomQuote::class);

        // On récupère un message aléatoire...
        $result = $random->getQuote();

        // On teste une assertion : pour le résultat renvoyé, est qu'il se trouve dans
        // la liste des messages possibles ($quotes)
        $this->assertContains($result, [
            "Hello Isengard",
            "Une belle journée pour coder",
            "La vie est belle",
            "Le succès est un mauvais professeur. Il pousse les gens intelligents à croire qu’ils sont infaillibles.",
            "Les programmes doivent être faits pour être lus par des gens, et occasionnellement pour être exécutés par des machines.",
            "Codez toujours comme si la personne qui allait maintenir votre code était un violent psychopathe qui sait où vous habitez.",
            "Mesurer la progression du développement d’un logiciel à l’aune de ses lignes de code revient à mesurer la progression de la construction d’un avion à l’aune de son poids",
            "Le gras c'est la vie",
            "Any fool can write code that a computer can understand",
            "First, solve the problem",
            "Knowledge is power."
        ]);
    }
}