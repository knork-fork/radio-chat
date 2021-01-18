<?php

namespace App\Repository;

use App\Entity\Friend;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    public function getFriends(User $user) : Array
    {
        return $this->createQueryBuilder("f")
            ->andWhere("f.userSource = :user")
            ->setParameter("user", $user)
            ->getQuery()
            ->getResult();
    }

    public function isFriend(int $userId, int $friendId) : bool
    {
        $friend = $this->createQueryBuilder("f")
            ->andWhere("f.userSource = :user")
            ->andWhere("f.userTarget = :friend")
            ->setParameter("user", $userId)
            ->setParameter("friend", $friendId)
            ->getQuery()
            ->getOneOrNullResult();
        
        return $friend != null;
    }

    // /**
    //  * @return Friend[] Returns an array of Friend objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Friend
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
