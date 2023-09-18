<?php

namespace App\Repository;

use App\Entity\Tricks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tricks>
 *
 * @method Tricks|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tricks|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tricks[]    findAll()
 * @method Tricks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TricksRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {

        parent::__construct($registry, Tricks::class);

    }

    /**
    * Enregistre une figure
    */
    public function save(Tricks $entity, bool $flush = false): void
    {

        
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

    }

    /**
    * Supprime une figure
    */
    public function remove(Tricks $entity, bool $flush = false): void
    {

        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

    }

    /**
    * Retourne une liste des figures 
    */
    public function findPaginatedTricks($offset, $limit)
    {

        return $this->createQueryBuilder('t')
            ->select('DISTINCT t')
            ->orderBy('t.create_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

    }

    /**
    * Permet de compter le nombre de figures
    */
    public function countAllTricks(): int
    {

        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

    }

}
