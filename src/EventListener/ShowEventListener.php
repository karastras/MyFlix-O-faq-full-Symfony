<?php

namespace App\EventListener;

use App\Entity\Show;
use App\Service\Slugger;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ShowEventListener {   

    private $slugger;
    public function __construct(Slugger $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * On intercepte l'objet Show un peu avant sa création,
     * pour ajout du slug
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function prePersist(LifecycleEventArgs $events)
    {
        // On récupère l'objet Show
        $show = $events->getObject();

        // Si on est pas une instance (object) de l'entity Show
        // on ne fait rien (on s'arrete là)
        if (!$show instanceof Show) {
            return;
        }

        // On slugify le titre
        $slug = $this->slugger->slugify($show->getTitle());
        $show->setSlug($slug);
    }

    /**
     * On intercepte l'objet Show un peu avant son update,
     * pour mise à jour du slug
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function preUpdate(LifecycleEventArgs $events)
    {
        $show = $events->getObject();

        // Si on est pas une instance (object) de l'entity Show
        // on ne fait rien (on s'arrete là)
        if (!$show instanceof Show) {
            return;
        }

        // On slugify le titre
        $slug = $this->slugger->slugify($show->getTitle());
        $show->setSlug($slug);
    }
}