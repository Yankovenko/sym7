<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findByTitleOrContent(string $query)
    {
        return $this->findByCriteria(
            self::createTitleOrContentCriteria($query)
        );
    }

    public static function createTitleOrContentCriteria(string $query): Criteria
    {
        return Criteria::create()
            ->where(
                Criteria::expr()->contains('title', $query)
            )
            ->orWhere(
                Criteria::expr()->contains('content', $query)
            );
    }

    protected function findByCriteria(Criteria $criteria)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')
            ->from($this->getClassName(),'p')
            ->addCriteria($criteria);
        $query = $qb->getQuery();
        return $query->getResult();
    }
}
