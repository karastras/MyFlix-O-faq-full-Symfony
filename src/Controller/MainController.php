<?php

namespace App\Controller;

use App\Service\RandomQuote;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    private $quoter;
    public function __construct(RandomQuote $quoter)
    {
        $this->quoter = $quoter;
    }

    #[Route('/', name: 'main_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('main/index.html.twig', [
            'quote' => $this->quoter->getQuote(),
        ]);
    }
}
