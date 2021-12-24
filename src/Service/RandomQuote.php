<?php

namespace App\Service;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class RandomQuote {
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token->getToken();        
    }

    public function getQuote() {
        $quotes = [
            "Hello Isengard",
            "Une belle journée pour coder",
            "La vie est belle",
            "Le succès est un mauvais professeur. Il pousse les gens intelligents à croire qu’ils sont infaillibles.",
            "Les programmes doivent être faits pour être lus par des gens, et occasionnellement pour être exécutés par des machines.",
            "Codez toujours comme si la personne qui allait maintenir votre code était un violent psychopathe qui sait où vous habitez.",
            "Mesurer la progression du développement d’un logiciel à l’aune de ses lignes de code revient à mesurer la progression de la construction d’un avion à l’aune de son poids",
            "Le gras c'est la vie",
            "Any fool can write code that a computer can understand",
            "First, solve the problem",
            "Knowledge is power."
        ];

        // Si je suis connecté, je personnalise la citation avec le login du User
        // On récupère l'utilisateur courant (connecté ou Anonimous)
        if ($this->token) {
            $user = $this->token->getUser();
            if ($user instanceof User && $this->token->isAuthenticated()) {
                foreach($quotes as $index=>$quote) {
                    // Bonjour Jeannete : Hello world
                    $quotes[$index] = "Bonjour {$user->getfirstname()} {$user->getlastname()} : ". $quotes[$index];
                }
            }
        }

        $randomIndex = array_rand($quotes);

        return $quotes[$randomIndex];
    }
}