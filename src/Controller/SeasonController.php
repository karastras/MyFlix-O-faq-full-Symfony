<?php

namespace App\Controller;

use App\Entity\Season;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeasonController extends AbstractController
{
    /**
     * Méthode permettant de créer une nouvelle saison
     * 
     * @Route("/season", name="season_create")
     */
    public function seasonCreate(): Response
    {
        // Pour créer une nouvelle entité, on commence par appeler le Manager
        // Entity Manager (em)
        $em = $this->getDoctrine()->getManager();

        $newsaison = new Season();
        $newsaison->setNumber(3);

        $releasedAT = new \DateTime('NOW');
        $newsaison->setReleasedAt($releasedAT);
        $newsaison->setCreatedAt($releasedAT);

        // En production ?
        $newsaison->setIsOnProduction(true);

        // On peut ensuite rajouter notre objet à notre piule d'objets à sauvegarder
        $em->persist($newsaison);

        // Dès qu'on est prêt à enregistrer les données, on appelle la méthode flush(push like)
        $em->flush();
        
        dd($newsaison);

        return $this->render('season/index.html.twig', [
            'controller_name' => 'SeasonController',
        ]);
    }
    /**
     * Méthode permettant d'afficher les détails d'une saison
     * 
     * @Route("/season/{id}", name="season_show", requirements={"id"="\d+"})
     */
    public function seasonShow(): Response
    {
        return $this->render('season/show.html.twig', [
            'controller_name' => 'SeasonController',
        ]);
    }
}
