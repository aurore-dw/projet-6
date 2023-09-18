<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Tricks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {

        parent::__construct($registry, Comment::class);

    }

    /**
    * Permet de récupérer la liste des commentaires par tricks
    */
    public function findPaginatedCommentsByTrick($trickId, $offset, $limit)
    {

        return $this->createQueryBuilder('c')
            ->innerJoin('c.trick', 't')
            ->andWhere('t.id = :trickId')
            ->setParameter('trickId', $trickId)
            ->orderBy('c.create_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

    }

    /**
    * Enregistre un commentaire
    */
    public function save(Comment $entity, bool $flush = false): void
    {

        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

    }

    /**
    * Supprime un commentaire
    */
    public function remove(Comment $entity, bool $flush = false): void
    {

        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

    }

    /**
    * Supprime les commentaires liés à un tricks lors de la suppression de ce dernier
    */
    public function deleteCommentsByTrick(Tricks $trick): void
    {

        $comments = $this->createQueryBuilder('c')
            ->delete()
            ->where('c.trick = :trick')
            ->setParameter('trick', $trick)
            ->getQuery()
            ->execute();

    }

}
