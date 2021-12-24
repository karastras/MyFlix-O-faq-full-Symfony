<?php 
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class de test pour les routes génériques (home, contact, cgu,...) de notre application
 */
Class MainControllerTest extends WebTestCase {

    /**
     * Méthode permettant de test la page d'accueil
     *
     * @return void
     */
    public function testPublicFunctionHomePage()
    {
        $client = static::createClient();

        // On simule l'affichage de la page d'accueil
        $client->request("GET", "/");

        $codeHTTP = $client->getResponse()->getStatusCode();

        // On vérifie que la page testée nous renvoie un code 200
        $this->assertEquals(200, $codeHTTP, "Cette page n'existe pas. Code Http {$codeHTTP}" );
    }

    /**
     * Test la présence ou non du titre "Regardez des séries TV en ligne"
     * sur la page d'accueil
     *
     * @return void
     */
    public function testHomePageTitle()
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $client->request('GET', '/');

        // Validate a successful response and some content
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Regardez des séries TV en ligne');
    }

    /**
     * On va simuler un click sur le bouton "commencer maintenant" et vérifier:
     * 1) Que la page que l'on obtient existe bien (code 200)
     * 2) Que la page que l'on obtient contient le titre = "Séries"
     *
     * @return void
     */
    public function testClickHomePageButton()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        
        // On va récupérer le bouton grâce à la méthode filter
        $link = $crawler->filter('div.bg-info a.btn-danger:contains(Commencez maintenant)')
                ->first()
                ->link();

        // On click sur le bouton: on est donc redirigé vers une page
        $crawlerLink = $client->click($link);
        
        // On vérifie si la page demandée existe: on vérifie que le code de retour
        // est bien le code 200, équivalent à 
        // $this->assertEquals(200, $codeHTTP, "Cette page n'existe pas. Code Http {$codeHTTP}" );
        $this->assertResponseIsSuccessful();

        // On vérifie que la page que l'on obtient contient un title = "Séries"
        $this->assertSelectorTextContains('h1', 'Séries');

    }
}