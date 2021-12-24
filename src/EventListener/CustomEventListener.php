<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Listener qui va écouter l'évenement kernel.request 
 * Voir la doc : https://symfony.com/doc/current/reference/events.html#kernel-request
 */
class CustomEventListener {

    /**
     * On récupère le service Logger pour collecter des informations "anonyme" sur les
     * personnes qui se connectent
     *
     * @param LoggerInterface $logger
     */
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Puisque l'on souhaite écouter l'évenement kernel.request
     * on va créér une méthode qui s'appelle onKernelRequest
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event) {
        // On récupère un objet de la class Request du composant HTTP Foundation
        $request = $event->getRequest();

        // Il n y a encore aucune réponse...chouette ! On ne surcharge pas notre
        // serveur (ni le cuisinier) si on a devant nous un mauvais client (mauvais payeur :())
        // Ici $response == null
        $response = $event->getResponse();

        // On récupère l'adresse IP de l'utilisateur
        $userIp = $request->getClientIp();

        // On va pouvoir stocker les adresses des clients dans un fichier de log
        $this->logger->info("L'utilisateur avec cette adresse IP a tenté de se connecter ". $userIp);

        // dump("On est au tout début de l'application O'flix", $response, $userIp);
    }
}