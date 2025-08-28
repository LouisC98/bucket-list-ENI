<?php

namespace App\Repository;

use App\Entity\Wish;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nette\Utils\Paginator;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

/**
 * @extends ServiceEntityRepository<Wish>
 */
class WishRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wish::class);
    }

    public function findByCriteria(string $search = "", array $orderBy = [], bool $isCompleted = null, bool $isPublished = true, int $userId = null): array
    {
        $qb = $this->createQueryBuilder('w');

        if ($search) {
            $qb->leftJoin('w.author', 'a')
                ->where('w.title LIKE :search')
                ->orWhere('a.firstName LIKE :search')
                ->orWhere('a.lastName LIKE :search')
                ->orWhere("CONCAT(a.firstName, ' ', a.lastName) LIKE :search")
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

        if (empty($orderBy)) {
            $qb->orderBy('RAND()');
        } else {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy('w.'.$field, $direction);
            }
        }

        return $qb->getQuery()
            ->getResult()
        ;
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
