<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findAllWithLibelle(QueryBuilder $queryBuilder = null){
        $query = $queryBuilder;

        dump($queryBuilder);
        if ($queryBuilder == null){
            $query = $this->createQueryBuilder('s');
        }
        return $query->select('s', 'e','co','o','l','i','v')
            ->from('App:Sortie','s')
            ->innerJoin('s.campusOrganisateur','co')
            ->innerJoin('s.organisateur','o')
            ->innerJoin('s.etat','e')
            ->innerJoin('s.lieu','l')
            ->innerJoin('l.ville','v')
            ->leftJoin('s.inscriptions','i')
            ->andWhere("CURRENT_DATE() <= DATE_ADD(s.dateDebut,1, 'month')")
            ->orderBy('s.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();

    }
    public function findAllWithEtat()
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'e')
            ->innerJoin('s.etat','e')
            ->andWhere("CURRENT_DATE() <= DATE_ADD(s.dateDebut,1, 'month')")
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
