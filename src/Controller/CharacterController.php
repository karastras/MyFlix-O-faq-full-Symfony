<?php

namespace App\Controller;

use App\Entity\Character;
use App\Form\CharacterType;
use App\Repository\CharacterRepository;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/character')]
class CharacterController extends AbstractController
{
    public function __construct(
        private ImageUploader $uploader
        ) {}

    #[Route('/list', name: 'character_index', methods: ['GET'])]
    public function index(CharacterRepository $characterRepository): Response
    {
        return $this->render('character/index.html.twig', [
            'characters' => $characterRepository->findAllByTitle('asc'),
        ]);
    }

    #[Route('/new', name: 'character_new', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, "Page réservée aux administrateurs");
        
        $character = new Character();
        $form = $this->createForm(CharacterType::class, $character);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère le contenu de l'image téléversée
            $pictureFile = $form->get('picture')->getData();

            // 1) On va sauvegarder l'image physique d'un personnage dans un sous-dossier
            // du dossier public
            try {
                // On s'assure qu'il n'y a pas d'erreur
                $pictureFilename = $this->uploader->upload($pictureFile);
                // 2) On sauvegardera aussi le nom de l'image uploadée en base de données
                /** @var string $pictureFilename */
                $character->setPictureFilename($pictureFilename);
            } catch (\Throwable $th) {
                // Sinon on peut rajouter un message d'erreur
                $this->addFlash('error', "Erreur dans l'upload de l'image");
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($character);
            $entityManager->flush();

            return $this->redirectToRoute('character_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('character/new.html.twig', [
            'character' => $character,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'character_show', methods: ['GET'])]
    public function show(Character $character): Response
    {
        return $this->render('character/show.html.twig', [
            'character' => $character,
        ]);
    }

    #[Route('/{id}/edit', name: 'character_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Character $character): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, "Page réservée aux administrateurs");
        
        $form = $this->createForm(CharacterType::class, $character);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère le contenu de l'image téléversée
            $pictureFile = $form->get('picture')->getData();

            // 1) On va sauvegarder l'image physique d'un personnage dans un sous-dossier
            // du dossier public
            try {
                // On s'assure qu'il n'y a pas d'erreur
                $pictureFilename = $this->uploader->upload($pictureFile);
                // 2) On sauvegardera aussi le nom de l'image uploadée en base de données
                /** @var string $pictureFilename */
                $character->setPictureFilename($pictureFilename);
            } catch (\Throwable $th) {
                // Sinon on peut rajouter un message d'erreur
                $this->addFlash('error', "Erreur dans l'upload de l'image");
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('character_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('character/edit.html.twig', [
            'character' => $character,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'character_delete', methods: ['POST'])]
    public function delete(Request $request, Character $character): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Page réservée aux administrateurs");

        if ($this->isCsrfTokenValid('delete'.$character->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($character);
            $entityManager->flush();
        }

        return $this->redirectToRoute('character_index', [], Response::HTTP_SEE_OTHER);
    }
}
