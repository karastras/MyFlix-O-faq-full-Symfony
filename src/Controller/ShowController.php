<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Show;
use App\Form\SeasonType;
use App\Form\ShowType;
use App\Repository\SeasonRepository;
use App\Repository\ShowRepository;
use App\Service\Slugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

// use App\Entity\Category;


/**
 * Regroupement des routes en Symfony
 */
#[Route("/show", name:"show_")]
class ShowController extends AbstractController
{
    private $slugger;
    public function __construct(Slugger $slugger)
    {
        $this->slugger = $slugger;
    }
    
    #[Route("/new", name:"new")]
    public function new(Request $request): Response
    {
        // Seul les users avec le Rôle ADMIN peuvent créer une nouvelle série
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // On créé une série vide, qui sera rempli par le formulaire Symfony
        $show = new Show();
        
        // On créé le formulaire
        // $form = $this->createForm(ShowType::class, $show, ['method'=>'GET']);
        // On va lier notre formulaire à notre objet $show,
        // ce qui permettra à Symfony (via la méthode handleRequest)
        // de mettre à jour notre objet vide avec les données transmises depuis 
        // le formulaire
        $form = $this->createForm(ShowType::class, $show, [
            // On désactive la validation HTML
            'attr' =>['novalidate'=>'novalidate']
        ]);
        
        // On intercepte les données soumises depuis le formulaire
        $form->handleRequest($request);
        
        // On va vérifier que le formulaire a bien été soumis (appui sur le bouton "Valider")
        if ($form->isSubmitted() && $form->isValid()) {
            // dd('Form submit !', $form->getData(), $show);
            // Puisque notre objet a été mis à jour par Symfony
            // on peut directement le sauvegarder sans passser par la méthode $form->getData()
            $title = $show->getTitle();
            $show->setSlug($this->slugger->slugify($title));
            $slug = $show->getSlug();
            $em = $this->getDoctrine()->getManager();
            $em->persist($show);
            $em->flush();
            
            // On ajoute un message flash            
            $this->addFlash('success', "La série {$title} a bien été créée");
            
            // On redirige vezrs la nouvelle série créé
            return $this->redirectToRoute('show_slug', ['slug'=>$slug]);
        }
        
        // On affiche le formulaire d'ajout
        return $this->render('show/new.html.twig', [
            // On transmet à twig une version  compréhensible du formulaire
            // en appellant la méthode createView
            'showForm' => $form->createView()
        ]);
        
    }
    
    #[Route('/{slug}/update', name:"update")]
    public function showUpdate(Show $show, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // On utilise le repository + l'id passé en paramètre
        // On récupère notre objet série
        // $repository = $this->getDoctrine()->getRepository(Show::class);
        // $show = $repository->find($id);
        // Solution 2 avec le Param Converter
        $form = $this->createForm(ShowType::class, $show);

        $form = $this->createForm(ShowType::class, $show, [
            // On désactive la validation HTML
            'attr' =>['novalidate'=>'novalidate']
        ]);

        $form->handleRequest($request);
        
        // On va vérifier que le formulaire a bien été soumis (appui sur le bouton "Valider")
        if ($form->isSubmitted() && $form->isValid()) {
            
            $title = $show->getTitle();
            $show->setSlug($this->slugger->slugify($title));
            $slug = $show->getSlug();
            $em = $this->getDoctrine()->getManager();
            
            // Le manager sait que la série existe déjà ( existence d'un objet show avec un show avec un id, ...), donc pas besoin de persist
            // $em->persist($show);
            
            $em->flush();
            
            // On ajoute un message flash            
            $this->addFlash('success', "La série {$show->getTitle()} a bien été mise à jour");
            
            // On redirige vezrs la série mise à jour
            // return $this->redirectToRoute('show_details', ['id'=>$show->getId()]);
            return $this->redirectToRoute('show_slug', ['slug'=>$slug]);
            
        }
        
        // On affiche le formulaire de mise à jour
        return $this->render('show/update.html.twig', [
            // On transmet à twig une version  compréhensible du formulaire
            // en appellant la méthode createView
            'showForm' => $form->createView(),
            'show'=> $show
        ]);
        
    }
    
    #[Route("/list", name:"list", priority:2)]
    public function showList(Request $request): Response
    {
        // Show::class == App\Model\Show
        /** @var ShowRepository $showRepository */
        $showRepository = $this->getDoctrine()->getRepository(Show::class);
        
        // On récupère la requete de recherche
        $search = $request->query->get('search');
        
        // $shows = $repository->findAll();
        // L'argument "asc" n'est pas nécesaire puisque par defaut $order dans le Repository
        // est à asc. Il présent pour l'exemple
        $shows = $showRepository->findAllByTitle($search, 'asc');
        
        /** @var ShowRepository $categoryRepository */
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);
        $categories = $categoryRepository->findAllByOrderLabel('asc');
        
        return $this->render('show/list.html.twig', [
            'shows' => $shows,
            'categories' => $categories,
        ]);
    }

    /**
     * Affiche le détails d'une série avec différentes informations (Catégories, saisons, ...)
     * en fonction du slug
     */
    #[Route('/{slug}', name:"slug")]
    public function showBySlug(Show $show)
    {
        // Doctrine va faire select * from show where slug={slug}
        // Il appelle la méthode FindOneBy(['slug'=>$slug])
        return $this->render('show/details.html.twig', [
            'show' => $show,
        ]);
    }

    // #[Route('/{id}/details', name:"details",  requirements: ['id' => '\d+'])]
    // public function showDetails($id): Response
    // {
    //     // Show::class == App\Model\Show
    //     /**
    //      * @var ShowRepository $repository
    //      */
    //     $repository = $this->getDoctrine()->getRepository(Show::class);
    //     // Le custom querie findShowWithDetails permets de d'afficher toutes les données
    //     // En une seul requête alors qu'avec la méthode précédente 4 requêtes étaient nécessaires
    //     // $show = $repository->find($id);
    //     $show = $repository->findShowWithDetails($id);
        
    //     if (!$show) {
    //         throw $this->createNotFoundException("La série $id n'existe pas!");
    //     }
        
    //     return $this->render('show/details.html.twig', [
    //         'show' => $show,
    //     ]);
    // }
    
    #[Route('/{slug}/season/new', name:"season_new", methods:['GET', 'POST'])]
    public function showAddSeason(Show $show, Request $request): Response
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // On créé une nouvelle saison vide
        // que l'on lie ensuite à la série courante
        $newSeason = new Season();
        $newSeason->setSeasonShow($show);
        
        // Création du formulaire pour ajouter une saison à notre série
        $form = $this->createForm(SeasonType::class, $newSeason, [
            // On empeche la validation HTML 5
            'attr' => ['novalidate' => 'novalidate']
        ]);
        
        $form->handleRequest($request);
        
        // On va vérifier que le formulaire a bien été soumis (appui sur le bouton "Valider")
        if ($form->isSubmitted() && $form->isValid()) {
            // On appelle l'entity Manager chargé de faire persister nos données
            $em = $this->getDoctrine()->getManager();
            
            // // On veut ensuite créer de nouvelles saisons pour cette série
            // // 1) On commence par créer une saison, avant de l'ajouter à la pile d'objet
            // // à sauvegarder
            $em->persist($newSeason);
            
            // On va ensuite lié la série $show à la saison $newSeason
            // Et on lie le personnage $character à la saison $newSeason
            // On met à jour la série (update) en lui attribuant une nouvelle saison
            // $show->addSeason($newSeason);
            
            // // On peut créer (une nouvelle saison) ou mettre à jour (la série) les données dont on a besoin
            $em->flush();
            
            // On ajoute un message flash            
            $this->addFlash('success', "Les éléments de la série {$show->getTitle()} ont bien été mis à jour");
            $slug = $show->getSlug();
            // On redirige vezrs la série mise à jour
            // return $this->redirectToRoute('show_details', ['id'=>$show->getId()]);
            return $this->redirectToRoute('show_slug', ['slug'=>$slug]);
        }        
        
        return $this->render('show/season_new.html.twig', [
            'form' => $form->createView(),
            'show' => $show,
        ]);
    }
    
    #[Route('/{slug}/season_update/{seasonId}', name:"season_update")]
    public function showUpdateSeason(Show $show, $seasonId, SeasonRepository $repository, Request $request): Response
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $season = $repository->find($seasonId);
        $form = $this->createForm(SeasonType::class, $season, [
            'attr' => ['novalidate' => 'novalidate']
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $show->getSlug();
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', "Les éléments de la saison {$season->getNumber()} ont bien été mis à jour");
            
            return $this->redirectToRoute('show_slug', ['slug'=>$slug]);
        }        
        
        return $this->render('show/season_new.html.twig', [
            'form' => $form->createView(),
            'show' => $show,
        ]);
    }
    
    #[Route('/{id}/delete', name:"delete",  requirements: ['id' => '\d+'])]
    public function showDelete(Show $show): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        // Solution 1 : on utilise le repository + l'id passé en paramètre
        // $repository = $this->getDoctrine()->getRepository(Show::class);
        // $show = $repository->find($id);
        // dd($id, $show);
        
        // Solution 2 : ParamConverter (injection de dépendance)
        // 1) On commence par récupérer l'objet (entité) à supprimer => (Param Converter)
        
        // 2) On appelle le manager : qui est appelé pour gérer les actions
        // critiques (ajout, suppression, mise à jour )
        $em= $this->getDoctrine()->getManager();
        
        // 3) On dit au manager notre intention de supprimer l'entité
        $em->remove($show);
        
        // 4) On supprime (Execution!)
        $em->flush();
        
        $this->addFlash('success', "La série \"{$show->getTitle()}\" a bien été supprimée");
        
        // 5) On redirige vers la liste des séries
        return $this->redirectToRoute('show_list');
    }
}


/////////////////////////////// Première version de show/create ////////////////////////////////////////////

// #[Route("/create", name:"create")]
// public function showCreate(): Response
// {
//     // Pour créer une nouvelle série, je vais faire appel à l'entity manager de Doctrine
//     $em = $this->getDoctrine()->getManager();

//     // On créé une nouvelle série
//     $friends = new Show();
//     $friends->setTitle('Friends !');
//     $friends->setTrailer('https://www.allocine.fr/video/player_gen_cmedia=19422840&cserie=49.html');
//     $friends->setSynopsis('Les péripéties de 6 jeunes newyorkais liés par une profonde amitié.');

//     // Je crée une nouvelle saison pour la série Friends
//     $friendsSaison1 = new Season();
//     $friendsSaison1->setNumber(1);

//     // Et je viens la lier à la série Friend
//     // Désormais, Friends aura une sasion numéro 1
//     $friends->addSeason($friendsSaison1);

//     // On va rajouter Friends à une pile d'objets à sauvegarder
//     $em->persist($friends);// similaire à un "git add"

//     // On créé une deuxième série
//     $doctorwho = new Show();
//     $doctorwho->setTitle('Doctor Who');
//     $doctorwho->setTrailer('https://www.allocine.fr/video/player_gen_cmedia=19422840&cserie=49.html');
//     $doctorwho->setSynopsis('Les péripéties de 6 jeunes newyorkais liés par une profonde amitié.');
//     // On va rajouter Doctor Who à une pile d'objets à sauvegarder
//     $em->persist($doctorwho);// similaire à un "git add"

//     //On va pouvoir sauvegarder nos datas en BDD
//     $em->flush();// similaire à un "git push"

//     return $this->render('show/index.html.twig', [
//         'controller_name' => 'ShowController',
//     ]);
// }



/////////////////////////////// Première version d'ajout de catégorie //////////////////////////////////////////
// /**
//  * @Route("/{id}/category/{categoryId}", name="add_category", requirements={"categoryId"="\d+"})
//  */
// public function showAddCategory($id, $categoryId)
// {
//     $this->denyAccessUnlessGranted('ROLE_ADMIN');
//     // On fait appelle au Manager
//     $em = $this->getDoctrine()->getManager();
    
//     // Je récupère la Série dont l'identifiant est égal à $id
//     $repositoryShow = $this->getDoctrine()->getRepository(Show::class);
//     $show = $repositoryShow->find($id);
    
//     // Je récupère la catégorie dont l'identifiant est égal à $categoryId
//     $repositoryCategory = $this->getDoctrine()->getRepository(Category::class);
//     $category = $repositoryCategory->find($categoryId);
    
//     // On va lier notre catégorie à la série
//     $show->addCategory($category);
    
//     // On demande ensuite de créer la relation en BDD
//     $em->flush();
    
//     // Redirection
//     return $this->redirectToRoute('show_details', ['id'=>$show->getId()]);
// }