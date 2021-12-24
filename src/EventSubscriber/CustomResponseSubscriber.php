<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CustomResponseSubscriber implements EventSubscriberInterface
{
    /**
     * Méthode permettant de modifier la réponse du client
     *
     * @param ResponseEvent $event
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {   
        // On récupère la réponse demandée initialement
        $responseInit = $event->getResponse();

        // On modifie la demande du client on affichant un message différent
        // de ce qu'il esperait
        $responseWarning = new Response("Désolé, vous n'avez pas le droit d'afficher cette page");
        // $event->setResponse($responseWarning);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.response' => 'onKernelResponse',
            //'kernel.request' => 'onKernelRequest',
        ];
    }
}