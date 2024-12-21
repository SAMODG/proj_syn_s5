<?php

namespace App\Repository;

use App\Entity\Dette;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dette>
 */
class DetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dette::class);
    }



    // Requête personnalisée pour trouver les dettes non soldées d'un client
    public function findNonSoldesByClient(Client $client)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.client = :client')
            ->andWhere('d.montantRestant > 0')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Dette[] Returns an array of Dette objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Dette
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
