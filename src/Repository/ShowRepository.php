<?php

namespace App\Repository;

use App\Entity\Show;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Show|null find($id, $lockMode = null, $lockVersion = null)
 * @method Show|null findOneBy(array $criteria, array $orderBy = null)
 * @method Show[]    findAll()
 * @method Show[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Show::class);
    }

    /**
     * Permet de créer (Build) une seule requete permettant
     * d'afficher :
     * - 1 Série (et les informations associées)
     * - Les saisons de la série
     * - Les catégories de la série
     * - Les personnages de la série
     *
     * @return Show
     */
    public function findShowWithDetails($id)
    {
        // 1) On appelle notre QueryBuilder
        // spl = SELECT*FROM show AS showdetails
        $queryBuilder = $this->createQueryBuilder('showdetails');

        // 2) On va venir rajouter des conditions
        $queryBuilder
            ->where('showdetails.id = :id')
            ->setParameter('id', $id);

            
        // En plus des informations sur la série, on veut également
        // une jointure pour récupérer les saisons    
        $queryBuilder->leftJoin('showdetails.seasons', 'season');

        // on veut égalament une jointure pour récupérer les catégories    
        $queryBuilder->leftJoin('showdetails.categories', 'category');

        // et les personnages    
        $queryBuilder->leftJoin('showdetails.characters', 'character');

        // on ajoute nos tables au select général
        $queryBuilder->addSelect('season, character, category');

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findAllByTitle($title, $order = '')
    {
        // Si le title n'est pas vide ===> On retourne les séries dont 
        // le title associé (like) à $title
        // createQueryBuilder => sql= select * from show
        $queryBuilder = $this->createQueryBuilder('show');

        if (!empty($title)) {
            // On a un title, on retorune donc les séries associées au title
            // On rajoute des conditions pour filtrer les séries associcées au title
            // sql = select * from show where title like "%$title%"
            // permettant de faire un "like sql"
            $queryBuilder->where(
                $queryBuilder->expr()->like('show.title', ':title')
            );

            $queryBuilder->setParameter('title', "%$title%");
        }
        $queryBuilder->orderBy('show.title', $order);
        // On récupère la réponse
        $query = $queryBuilder->getQuery();

        // On retourne les résultats trouvés
        return $query->getResult();
    }

    // /**
    //  * @return Show[] Returns an array of Show objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Show
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
