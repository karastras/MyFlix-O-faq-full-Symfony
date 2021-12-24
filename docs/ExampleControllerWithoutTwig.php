<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Character;
use App\Entity\Season;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route("/category", name:"category_")]
class CategoryController extends AbstractController
{
/**
     * Méthode permettant d'accéder au formulaire d'ajout une nouvelle catégorie
     */
    #[Route("/new", name:"new")]
    public function categoryNew(): Response
    {
        return $this->render('category/new.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    /**
     * Méthode permettant de créer/valider le formulaire d'ajout d'une nouvelle catégorie
     */
    #[Route("/create", name:"create", methods:"POST")]
    public function categoryCreate(Request $request): Response
    {
       // dd('ajout catégorie', $request);
       // dd('ajout catégorie', $request->request->get('title'));

        // On récupère le nom de la catégorie via la propriété public request de l'objet $request
        $categoryTitle = $request->get('title');

        // On appelle le Manager pour gérer la sauvegarde d'une catégorie
        $em = $this->getDoctrine()->getManager();

        // On créé un nouvel objet category
        $newCategory = new Category();
        $newCategory->setTitle($categoryTitle);

        // Je rajoute à la pile : je dis mon intention de créer uen nouvelle catégorie
        // la catégorie n'existe pas encore en BDD
        $em->persist($newCategory);

        // On exécute la sauvegarde de la catégorie en BDD
        $em->flush();

        return $this->redirectToRoute('category_list');
    }

    /**
     * Méthode pour supprimer une catégorie
     *
     * @Route("/{id}/delete", name="delete", requirements={"id"="\d+"})
     */
    public function categoryDelete(Category $category): Response
    {
        // Solution 1
        // $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        // dd($category);

        // Solution 2: Param converter (injection de dépendance)
        // dd($category);

        $em= $this->getDoctrine()->getManager();

        $em->remove($category);

        $categoryTitle = $category->getTitle();

        $em->flush();

        $this->addFlash('success', "La catégorie {$categoryTitle} a bien été supprimée");

        return $this->redirectToRoute(('category_list'));
    }

    // Fonction provenant de la permeiere version du showcontroller
    public function showAddSeason($id)
    {
        
        // Show::class == App\Model\Show
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Show::class);

        // Je récupère la Série dont l'identifaint est égal à $id
        $show = $repository->find($id);

        // On veut ensuite créer de nouvelles saisons pour cette série
        // 1) On commence par créer une saison, avant de l'ajouter à la pile d'objet
        // à sauvegarder
        $newSeason = new Season();
        $newSeason->setNumber(2);
        $em->persist($newSeason);

        $character = new Character();
        $character->setFullname("Robin");
        $em->persist($character);


        // On va ensuite lié la série $show à la saison $newSeason
        // Et on lie le personnage $character à la saison $newSeason
        // On met à jour la série (update) en lui attribuant une nouvelle saison
        $show->addSeason($newSeason);
        $show->addCharacter($character);

        // On peut créer (une nouvelle saison) ou mettre à jour (la série) les données dont on a besoin
        $em->flush();

        

        return $this->render('show/details.html.twig', [
            'show' => $show,
        ]);
    }
}

// expente de Voter -> User fais en cours 

<?php
namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter {

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * La méthode qui est appellée au moment du test sur l'objet et l'opération
     * que l'on souhaite autorisé (appel de isGranted)
     * 
     * Décide de si le Voter concernée est habilitée à traiter la demande
     *
     * @param string $attribute
     * @param [type] $subject
     * @return void
     */
    protected function supports(string $attribute, $subject) {
        // On veut savoir si pour un utilisateur donné (connecté ou non) on a le droit
        // de modifer (update) une question
        // $attribute = opération à effectuer = 'update'
        // $subject = objet concerné par l'opération = $question

        // if ($attribute !== 'update') {
        //     return false;
        // }

        // if ($attribute !== 'update' && $attribute !== 'delete') {
        //     return false;
        // }

        // return in_array($attribute, ['update', 'delete']) && $subject instanceof Question;

        if (!in_array($attribute, ['update','delete'])) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * Une fois passé la barrière de la méthode supports, la méthode voteOnAttribute
     * permet de mettre à logique permettant de décider si pour un subject (un objet) donné, 
     * on peut appliquer l'attribut (l'opération : update, delete) en question
     * 
     * @param string $attribute
     * @param [type] $subject
     * @param TokenInterface $token
     * @return void
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token) {
        // On récupère l'utilisateur courant (connecté ou Anonimous)
        $user = $token->getUser();

        if (!$user instanceof User) {
            // On s'assure que l'utilisateur est bien connecté (et donc de type User)
            // Si non connecté (Anonymous), on retourne false
            return false;
        }
        
        // 1) On vérifie que l'utilisateur courant est identique au propriétaire de la question
        if ($user === $subject) {
            return true;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }
        
        return false;
    }
}