<?php

namespace App\Repository;

use App\Entity\AttributeValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AttributeValue>
 *
 * @method AttributeValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttributeValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttributeValue[]    findAll()
 * @method AttributeValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeValue::class);
    }

    public function add(AttributeValue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AttributeValue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search(string $searchString): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.attributableId')
            ->where("a.tags LIKE :term ESCAPE '!'")
            ->setParameter('term', $this->makeLikeParam($searchString));
        return $qb->getQuery()->getResult();
    }

    /**
     * Format a value that can be used as a parameter for a DQL LIKE search.
     *
     * $qb->where("u.name LIKE (:name) ESCAPE '!'")
     *    ->setParameter('name', $this->makeLikeParam('john'))
     *
     * NOTE: You MUST manually specify the `ESCAPE '!'` in your DQL query, AND the
     * ! character MUST be wrapped in single quotes, else the Doctrine DQL
     * parser will throw an error:
     *
     * [Syntax Error] line 0, col 127: Error: Expected Doctrine\ORM\Query\Lexer::T_STRING, got '"'
     *
     * Using the $pattern argument you can change the LIKE pattern your query
     * matches again. Default is "%search%". Remember that "%%" in a sprintf
     * pattern is an escaped "%".
     *
     * Common usage:
     *
     * ->makeLikeParam('foo')         == "%foo%"
     * ->makeLikeParam('foo', '%s%%') == "foo%"
     * ->makeLikeParam('foo', '%s_')  == "foo_"
     * ->makeLikeParam('foo', '%%%s') == "%foo"
     * ->makeLikeParam('foo', '_%s')  == "_foo"
     *
     * Escapes LIKE wildcards using '!' character:
     *
     * ->makeLikeParam('foo_bar') == "%foo!_bar%"
     *
     * @param string $search Text to search for LIKE
     * @param string $pattern sprintf-compatible substitution pattern
     * @return string
     */
    protected function makeLikeParam(string $search, string $pattern = '%%%s%%'): string
    {
        /**
         * Function defined in-line so it doesn't show up for type-hinting on
         * classes that implement this trait.
         *
         * Makes a string safe for use in an SQL LIKE search query by escaping all
         * special characters with special meaning when used in a LIKE query.
         *
         * Uses ! character as default escape character because \ character in
         * Doctrine/DQL had trouble accepting it as a single \ and instead kept
         * trying to escape it as "\\". Resulted in DQL parse errors about "Escape
         * character must be 1 character"
         *
         * % = match 0 or more characters
         * _ = match 1 character
         *
         * Examples:
         *      gloves_pink   becomes  gloves!_pink
         *      gloves%pink   becomes  gloves!%pink
         *      glo_ves%pink  becomes  glo!_ves!%pink
         *
         * @param string $search
         * @return string
         */
        $sanitizeLikeValue = function ($search) {
            $escapeChar = '!';

            $escape = [
                '\\' . $escapeChar, // Must escape the escape-character for regex
                '\%',
                '\_',
            ];
            $pattern = sprintf('/([%s])/', implode('', $escape));

            return preg_replace($pattern, $escapeChar . '$0', $search);
        };

        return sprintf($pattern, $sanitizeLikeValue($search));
    }


//    /**
//     * @return AttributeValue[] Returns an array of AttributeValue objects
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

//    public function findOneBySomeField($value): ?AttributeValue
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
