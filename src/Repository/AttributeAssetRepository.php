<?php

namespace App\Repository;

use App\Entity\Product\ProductAttributeAsset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductAttributeAsset>
 *
 * @method ProductAttributeAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductAttributeAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductAttributeAsset[]    findAll()
 * @method ProductAttributeAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeAssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductAttributeAsset::class);
    }

    public function add(ProductAttributeAsset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductAttributeAsset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AttributeAsset[] Returns an array of AttributeAsset objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AttributeAsset
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
