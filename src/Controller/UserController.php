<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserAccountAddType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
  * Toutes les pages du userController ne sont accessible que par les personnes 
  * connectées
  */
#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
    */
    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $user->getPassword();
            // Je Hash (encode en v.4 symfony) le MDP avec la method hashPassword et je le stocke
            // dans $hashedPassword
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            // J'ajooute le mdp hasher a la propriété password du user
            $user->setPassword($hashedPassword); 
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "LE COMPTE DE {$user->getFirstname()} {$user->getLastname()} A BIEN ETE CREE");

            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @IsGranted("user_show", subject="user")
     */
    #[Route('/{id}', name: 'user_show', methods: ['GET'],  requirements: ['id' => '\d+'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    
    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'],  requirements: ['id' => '\d+'])]
    public function edit(Request $request, User $user, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Cette méthode n'est accessible qu'aux personnes ayant le rôle de USER
        // Si le user n'a pas de rôle, on est rediriger sur la page login
        $this->denyAccessUnlessGranted('user_update', $user);

        $form = $this->createForm(UserType::class, $user, [
            'attr' => ['novalidate' => 'novalidate']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Je stocke le mot de passe en clair
            $plainPassword = $user->getPassword();
            // Je Hash (encode en v.4 symfony) le MDP avec la method hashPassword et je le stocke
            // dans $hashedPassword
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            // J'ajooute le mdp hasher a la propriété password du user
            $user->setPassword($hashedPassword); 
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @IsGranted("user_delete", subject="user")
    */
    #[Route('/{id}', name: 'user_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }
}
