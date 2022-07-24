<?php

namespace App\Repository\Campaign;

use App\Entity\Campaign\CampaignAttributeTab;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CampaignAttributeTab>
 *
 * @method CampaignAttributeTab|null find($id, $lockMode = null, $lockVersion = null)
 * @method CampaignAttributeTab|null findOneBy(array $criteria, array $orderBy = null)
 * @method CampaignAttributeTab[]    findAll()
 * @method CampaignAttributeTab[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampaignAttributeTabRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CampaignAttributeTab::class);
    }

    public function add(CampaignAttributeTab $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CampaignAttributeTab $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CampaignAttributeTab[] Returns an array of CampaignAttributeTab objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CampaignAttributeTab
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
