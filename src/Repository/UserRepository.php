<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function search($options): ?array
    {
        $qb = $this->createQueryBuilder('u');
        
        if(isset($options['where']['id']))
        {
            $qb->andWhere('u.id = :id');
            $qb->setParameter('id', $options['where']['id']);
        }
        else
        {
            $qb->andWhere('u.roles NOT LIKE :role');
            $qb->setParameter(':role', '%ROLE_ADMIN%');
        }

        $query = $qb->getQuery();
        $result = $query->getResult();

        if(isset($options['where']['created_at']) || isset($options['where']['quiz']))
        {
            $criteria = Criteria::create();
            (isset($options['where']['quiz'])) ? $criteria->andWhere(Criteria::expr()->eq('quiz', $options['where']['quiz'])) : null;
            (isset($options['where']['created_at'])) ? $criteria->andWhere(Criteria::expr()->gt('created_at', $options['where']['created_at']['start'])) : null;
            (isset($options['where']['created_at'])) ? $criteria->andWhere(Criteria::expr()->lt('created_at', $options['where']['created_at']['end'])) : null;

            foreach ($result as $key => $value) 
            {
                $result[$key]->setSolvedQuizzes($result[$key]->getSolvedQuizzes()->matching($criteria));
            }
        }

        if(isset($options['where']['created_at']) || isset($options['where']['category']))
        {
            $criteria = Criteria::create();
            (isset($options['where']['category'])) ? $criteria->andWhere(Criteria::expr()->eq('category', $options['where']['category'])) : null;
            (isset($options['where']['created_at'])) ? $criteria->andWhere(Criteria::expr()->gt('created_at', $options['where']['created_at']['start'])) : null;
            (isset($options['where']['created_at'])) ? $criteria->andWhere(Criteria::expr()->lt('created_at', $options['where']['created_at']['end'])) : null;

            foreach ($result as $key => $value) 
            {
                $result[$key]->setRequestSupportMaterials($result[$key]->getRequestSupportMaterials()->matching($criteria));
            }
        }

        return $result;
    }

    public function findTraders(): ?array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere('u.roles NOT LIKE :role');
        $qb->setParameter(':role', '%ROLE_ADMIN%');

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findActiveAdmins(): ?array
    {
        $qb = $this->createQueryBuilder('u');

        $qb->andWhere('u.roles LIKE :role');
        $qb->setParameter(':role', '%ROLE_ADMIN%');

        $qb->andWhere('u.isActive = :isActive');
        $qb->setParameter(':isActive', true);

        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
