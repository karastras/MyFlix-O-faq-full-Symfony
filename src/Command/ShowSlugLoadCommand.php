<?php

namespace App\Command;

use App\Entity\Show;
use App\Service\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    // On indique le nom de la commande
    // php bin/console app:show:slug:load:command
    name: 'app:show:slug',
    description: 'Mets à jour les show dans la BDD en ajoutant un slug',
)]
class ShowSlugLoadCommand extends Command
{
    // Quand on appelle des services en dehors d'un Controller on doit passer par un __construct
    private $manager;

    public function __construct(EntityManagerInterface $entityManager, Slugger $slugger)
    {
        $this->manager = $entityManager;
        $this->slugger = $slugger;
        // Sans l'appel du constructeur parent, Symfony nous donne un erreur
        //  Command class "App\Command\ShowSlugLoadCommand" is not correctly initialized. You probably forgot to call the parent constructor.
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // ->addArgument('showName', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    /**
     * Cette méthode permet d'implémenter la logique de notre commande.
     * C'est ici que l'on met en place ce que fait notre commande.active
     * 
     * Dans ce cas, on va mettre à jour les lugs de toutes les séries
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // 1) On va commencer par récupérer toutes les étapes
        $shows = $this->manager->getRepository(Show::class)->findAll();

        //dd($shows);

        // 2) On met à jour les slugs de chaque série
        foreach ($shows as $show) {
            //$io->note($show->getTitle());
            $title = $show->getTitle();
            // $io->text('Mise à jour de la série:' . $title);

            // Créé un slug à partir du title et mettre notre série
            $slug = $this->slugger->slugify($title);
            $show->setSlug($slug);
            
            $io->text("Mise à jour du slug de la série ". $title .": ". $slug);
        }
        
        // 3) On met à jour les données en base (flush)
        $this->manager->flush();


        $io->success('Mise à jour effectué avec succès');

        return Command::SUCCESS;
    }
}
