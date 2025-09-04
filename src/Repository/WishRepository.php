<?php

namespace App\Repository;

use App\Entity\Wish;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wish>
 */
class WishRepository extends ServiceEntityRepository
{
    public const WISHES_PER_PAGE = 6;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wish::class);
    }

    public function getWishPaginator(string $search = "", array $orderBy = [], bool $isCompleted = null, int $categoryId = null, bool $isPublished = true, int $userId = null, int $offset = 0): Paginator
    {
        $qb = $this->createQueryBuilder('w');

        if ($search) {
            $qb->leftJoin('w.author', 'a')
                ->where('w.title LIKE :search')
                ->orWhere('a.pseudo LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($isPublished) {
            $qb->andWhere('w.isPublished = :isPublished')
                ->setParameter('isPublished', true);
        }

        if ($isCompleted !== null) {
            $qb->andWhere('w.isCompleted = :isCompleted');
            $qb->setParameter('isCompleted', $isCompleted);
        }

        if ($userId !== null) {
            $qb->andWhere('w.author = :userId');
            $qb->setParameter('userId', $userId);
        }

        if ($categoryId !== null) {
            $qb->andWhere('w.category = :categoryId');
            $qb->setParameter('categoryId', $categoryId);
        }

        if (empty($orderBy)) {
            $qb->orderBy('w.createdAt', 'DESC');
        } else {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy('w.'.$field, $direction);
            }
        }

        $qb->setMaxResults(self::WISHES_PER_PAGE)
            ->setFirstResult($offset);

        return new Paginator($qb->getQuery());
    }

//    public function findAllWithLimit(int $limit = 10, int $offset = 0, ?string $search = null): array
//    {
//        $qb = $this->createQueryBuilder('w')
//            ->setMaxResults($limit)
//            ->setFirstResult($offset)
//            ->orderBy('w.createdAt', 'DESC');
//
//        if ($search) {
//            $qb->andWhere('w.title LIKE :search')
//                ->setParameter('search', '%' . $search . '%');
//        }
//
//        return $qb->getQuery()->getResult();
//    }

    //    /**
    //     * @return Wish[] Returns an array of Wish objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Wish
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
