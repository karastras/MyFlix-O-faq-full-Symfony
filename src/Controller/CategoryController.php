<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route("/category", name:"category_")]
class CategoryController extends AbstractController
{
    /**
     * Affiche un formulaire HTML permettant la création d'une nouvelle catégorie
     * Et si formulaire soumis, sauvegarde les données
     */
    #[Route("/new", name:"new")]
    public function categoryNew(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, "Page réservée aux administrateurs");

        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        // Va intercepter les données soumises (request) via le formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On sauvegarde la nouvelle catégorie
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            $this->addFlash("success","la catégorie {$category->getTitle()} a bien été créée");

            return $this->redirectToRoute('category_list');
        }

        return $this->render('category/new.html.twig', [
            'categoryForm' => $form->createView(),
        ]);
    }


    /**
     * Création d'une nouvelle catégorie en fonction de données
     * transmises depuis un formulaire
     * 
     * @Route("/{id}/update", name="update", methods={"POST", "GET"}, requirements={"id"="\d+"})
     */
    public function categoryUpdate(Category $category, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, "Page réservée aux administrateurs");

        // On appelle le formulaire de catégory (CategoryType)
        $form = $this->createForm(CategoryType::class, $category, [
            // On empeche la validation HTML 5
            'attr' => ['novalidate' => 'novalidate']
        ]);

        // On intercepte les données du formulaire
        $form->handleRequest($request);

        // On s'assure de la "validité" des données transmises
        if ($form->isSubmitted() && $form->isValid()) {
            // On appelle le manager pour gérer la sauvegarde d'une catégorie
            $em = $this->getDoctrine()->getManager();
    
            // La catégorie est déjà connue de Doctrine, pas besoin d'appeller la méthode
            // persist
            // $em->persist($category);
    
            // On execute la sauvegarde de la catégorie en BDD
            $em->flush();

            // Message de success
            $this->addFlash('success', "La catégorie {$category->getTitle()} a bien été modifiée");

            // Redirection vers la liste des catégories
            return $this->redirectToRoute('category_list');
        }


        return $this->render('category/update.html.twig', [
            'categoryForm' => $form->createView(),
            'category' => $category
        ]);
    }

    /**
     * Méthode permettant d'afficher les détails d'une catégorie
     * 
     * @Route("/{id}", name="show", requirements={"id"="\d+"})
     */
    public function categoryShow($id): Response
    {
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $category = $repository->find($id);

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * Méthode permettant d'afficher la liste de toutes les catégories
     */
    #[Route("/list", name:"list")]
    public function categoryList(CategoryRepository $repository): Response
    {
        /**
         * Astuce pour aider VScode à comprendre d'où vient le $repository
         * @var CategoryRepository $repository
         */
        //$repository = $this->getDoctrine()->getRepository(Category::class);
        $category = $repository->findAllByOrderLabel('asc');
        // $category = $repository->findAll();

        return $this->render('category/list.html.twig', [
            'categories' => $category,
        ]);
    }

    /**
     * Méthode permettant de supprimer une catégorie et d'alerter au cas où
     * une/des séries sont rattachées à la catégorie
     * 
     * @Route("/{id}/delete", name="delete", requirements={"id"="\d+"})
     */
    public function categoryDelete(Category $category) {
        
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Page réservée aux administrateurs");
        
        // On vérifie si la catégorie est associée à des séries
        if ($category->getShows()->isEmpty() === false) {
            // Si la catégorie est liée à une ou plusieurs séries
            // on affiche un message d'erreur
            $this->addFlash('danger', "La catégorie {$category->getTitle()} ne peut être supprimée, car 1 ou plusieurs séries l'utilisent");
        } else {
            // La catégorie est vide : on peut la supprimer

            // On appelle l'entity manager pour gérer la suppression de l'objet
            $em = $this->getDoctrine()->getManager();

            // On signale à doctrine (Manager) notre intention de supprimer la catégorie
            $em->remove($category);

            // On execute la suppresion
            $em->flush();

            // On informe de la suppression
            $this->addFlash('success', "La catégorie {$category->getTitle()} a bien été supprimée");
        }

        // On redirige vers la liste des catégories
        return $this->redirectToRoute(('category_list'));
    }
}
