<?php

namespace App\Command;

use App\Entity\Show;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:show:infos',
    description: 'Mise à jour du score de toute les séries',
)]
class ShowRatingCommand extends Command
{
    private $manager;
    private $client;
    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $client)
    {
        $this->manager = $entityManager;
        $this->client = $client;

        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // 1) On va récupérer l'ensemble des séries en BDD
        $shows = $this->manager->getRepository(Show::class)->findAll();

        // URL de l'api
        $apiURL = "http://www.omdbapi.com/";
        $apiKey = "6c9bc939";

        foreach($shows as $show){
            // 2) Pour chaque série, on va aller chercher sur IMDB le score
            $title = $show->getTitle();

            // Je passe en mode client, comme si je saisissais dans le navigateur (client)
            // l'URL suivant : GET https://www.omdbapi.com/?apikey=6c9bc939&t=friends
            $response = $this->client->request(
                'GET',
                $apiURL,
                [
                    'query' => [
                        'apiKey' => $apiKey,
                        't' => $title,
                    ],
                ]
            );

            // On transforme la réponse en tableau associatif
            $content = $response->toArray();

            // Est-ce qu'il y a une erreur ?
            if (isset($content['Error'])) {
                // J'ai une erreur : La série n'existe pas ou autre (Quota de req. dépassé)
                $io->error("La série {$title} n'existe pas sur IMDBAPI. Details de l'erreur : ".$content['Error']);
            } else {
                // Pas d'erreur, on exploite les données reçues
                // Processing responses : https://symfony.com/doc/current/http_client.html#processing-responses
                
                $io->text("Mise à jour du score de la série {$title} effectuée !");
                
                // 3) On met à jour le score dans la série
                $show->setRating(floatval($content['imdbRating']));

                // 3') On met à jour le poster de la série
                $show->setPoster($content['Poster']);
            }
        }

        // 4) On va sauvegarde l'ensemble des séries en BDD
        $this->manager->flush();

        $io->success("La mise à jour des séries a bien été effectuée");

        return Command::SUCCESS;
    }
}
 