<?php

namespace App\Repository;

use App\Entity\Participiant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participiant>
 *
 * @method Participiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participiant[]    findAll()
 * @method Participiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participiant::class);
    }

//    /**
//     * @return Participiant[] Returns an array of Participiant objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Participiant
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
