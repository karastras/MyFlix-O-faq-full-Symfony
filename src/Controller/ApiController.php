<?php

namespace App\Controller;

use App\Entity\Show;
use App\Repository\ShowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/show/list', name: 'show_list')]
    public function showList(ShowRepository $repository): Response
    {
        $shows = $repository->findAll();
        return $this->json($shows, 200, [], [
            'groups' => 'show_list',
        ]);
    }

    #[Route('/show/{id}/detail', name: 'show_detail', requirements: ['id' => '\d+'])]
    public function showDetail(Show $show): Response
    {
        return $this->json($show, 200, [], [
            'groups' => 'show_list',
        ]);
    }

    #[Route('/show/new', name: 'show_new', methods: ['POST'])]
    public function showAdd(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // On récupère les données au format JSON
        $dataJSON = $request->getContent();

        // On désérialize les données reçues
        // Arguments de la méthode deserialize
        // $data : les données transmises
        // string $type : le type de l'objet 
        // string $format: le format de transfert
        // array $context = []
        $show = $serializer->deserialize($dataJSON, Show::class, 'json', []);

        // On valide les données reçues avant de les sauvegarder grâce au Validator
        $errors = $validator->validate($show);
        $totalErrors = count($errors);
        $success = false;
        $message = 'La série a bien été créé';

        if ($totalErrors > 0) {
            // Si on a des erreurs, alors on ne fait aps de sauvegarde
            // et on prévient l'utilisateur
            $message = "Il y a {$totalErrors} erreur(s) dans votre requête";
        } else {
            $success = true;
            // On sauvegarde la série
            $em = $this->getDoctrine()->getManager();
            $em->persist($show);
            $em->flush();
        }

        // On retourne un message pour dire que tout s'est bien passé
        return $this->json([
            'success' => $success,
            'message' => $message,
            'errors' => $errors,
        ]);
    }
}
