<?php

namespace App\Repository;

use App\Entity\Feature;
use App\Entity\IntValue;
use App\Entity\RealValue;
use App\Entity\ScalarValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\FeaturePossibleValue;

/**
 * @method Feature|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feature|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feature[]    findAll()
 * @method Feature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feature::class);
    }

     /**
      * @return Feature[]
      * @todo
      */
    public function allFeaturesWithPossibleValues(): array
    {
        $features = $this->createQueryBuilder('f')
            ->addSelect('f')
            ->innerJoin(FeaturePossibleValue::class, 'pv')
            ->addSelect('pv')
            ->innerJoin(ScalarValue::class, 'sv')
            ->addSelect('sv')
            ->innerJoin(IntValue::class, 'iv')
            ->addSelect('iv')
            ->innerJoin(RealValue::class, 'rv')
            ->addSelect('rv')
            ->getQuery()
            ->getResult();

        return array_filter($features, static fn ($feature) => $feature instanceof Feature);
    }
}
