# Custom queries

Jusque là, on a pas eu besoin de choses un peu exotique (grâce aux Repositories)...Mais si on veut aller plus loin ?
Ex : data trier sur title par ordre alphabétique et limité au 3 premiers résultats

## Category : Affichage des catégories par trier par label
```php

    public function findAllOrderedByTitle() {
        // On crée "l'usine" à requete
        $queryBuilder = $this->createQueryBuilder('category');

        // fabrique une requete personnalisée
        $queryBuilder->orderBy('category.title', 'asc');

        // a la fin je recupère a la requete fabriquée
        $query = $queryBuilder->getQuery();

        // j'execute la requete pour en recupérer les resultats
        // getResult me renvoi une LISTE des resultats
        return $query->getResult();
    }

```

## Show : affichage des détails d'un show en une seule requete
```php
    // cette méthode doit me renvoyer une series (qui correspond a l'id en parametre)
    // cette série doit contenir les objet liés
    // exemple : SI $id contient la valeur 6
    public function findShowWithAllDetails($id) {
        // On commence par faire appelle au gestionnaire de requete perso
        $queryBuilder = $this->createQueryBuilder('show');

        // On rajoute nos conditions : je precise que je souhaite recupérer un element grace a son ID
        $queryBuilder->where('show.id = :id')->setParameter('id', $id);
        // maintenant le query builder va me donner une requete du genre :
        // SELECT * FROM show WHERE show.id = 6

        // On rajoute les saisons de la série
        $queryBuilder->leftJoin('show.seasons', 'season');
        $queryBuilder->addSelect('season');


        // On rajoute les saisons de la série
        $queryBuilder->leftJoin('show.categories', 'category');
        $queryBuilder->addSelect('category');

        // On trie pas title
        $queryBuilder->addOrderBy('category.title', 'desc');

        // On retourne le résultats
        return $queryBuilder->getQuery()->getOneOrNullResult();

    }
```

## Formulaire de recherche (GET) : Série en fonction de son title
```php
    public function findByTitle($search)
    {
        // Test 1
        $queryBuilder = $this->createQueryBuilder('show');

        $queryBuilder->leftJoin('show.categories', 'category');

        if(!empty($search)) {
            // WHERE show LIKE :search
            // $queryBuilder->where(
            //     $queryBuilder->expr()->orX(
            //         $queryBuilder->expr()->like('show.title', ':search'),
            //         $queryBuilder->expr()->like('category.title', ':search')
            //     )
            // );

            $queryBuilder->where(
                $queryBuilder->expr()->like('show.title', ':search')
            );

            // WHERE show LIKE '%star%'
            $queryBuilder->setParameter('search', "%$search%");
        }

        $queryBuilder->addOrderBy('show.title');

        // Test 2
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
```