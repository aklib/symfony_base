<?php /** @noinspection PhpUnused */
/** @noinspection PhpUnusedPrivateMethodInspection */

/**
 * Class AbstractAttributeManager
 * @package App\Bundles\Attribute\Manager
 *
 * since: 29.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Manager;

use App\Bundles\Attribute\Entity\AttributableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Elastica\Aggregation\Terms;
use Elastica\Index;
use Elastica\Query;
use Exception;
use FOS\ElasticaBundle\Index\IndexManager;
use Laminas\Json\Json;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractElasticaAttributeManager extends AbstractAttributeManager
{
    public const ATTRIBUTE_FIELD = 'attributes';
    private IndexManager $indexManager;


    public function __construct(Security $security, FlashBagInterface $flashBag, EntityManagerInterface $em, IndexManager $indexManager)
    {
        parent::__construct($security, $flashBag, $em);
        $this->indexManager = $indexManager;
    }

    /**
     * Unique key for document e.g. product_20,  user_profile_1
     * @param AttributableEntity|null $entity
     * @param array|null $docData
     * @return string
     */
    protected function getDocumentId(AttributableEntity $entity = null, array $docData = null): string
    {
        if ($entity instanceof AttributableEntity) {
            return $this->getScope($entity) . '_' . $entity->getId();
        }
        if (is_array($docData) && array_key_exists('scope', $docData)) {
            return $docData['scope'] . '_' . $docData['id'];
        }
        if (is_array($docData) && array_key_exists(self::ATTRIBUTE_FIELD, $docData) && array_key_exists('parent', $docData[self::ATTRIBUTE_FIELD])) {
            return $docData[self::ATTRIBUTE_FIELD]['parent'];
        }
        return '';
    }

//============================== ATTRIBUTE VALUES FORMAT/CONVERT ==============================

    /**
     * Loads elastica documents.
     * Documents can be loaded subsequently for lazy loaded entities
     * @param bool $asDocument
     * @return array
     */
    protected function getDocuments(bool $asDocument = false): array
    {
        if ($this->entities->isEmpty()) {
            return [];
        }
        $entities = new ArrayCollection();

        /** @var AttributableEntity $entity */
        foreach ($this->entities as $entity) {
            $key = $this->getDocumentId($entity);
            if (in_array($key, $this->initialisedEntities, true)) {
                continue;
            }
            $entities->add($entity);
            $this->initialisedEntities[] = $key;
        }
        if ($entities->isEmpty()) {
            return [];
        }
        $documents = [];
        // load documents from elasticsearch
        $query = $this->getQuery($entities);
        $query->setSize(9999);
        try {
            if ($asDocument) {
                $docs = $this->getIndex()->search($query)->getDocuments();
                foreach ($docs as $doc) {
                    $documents[$doc->getId()] = $doc;
                }
            } else {
                $response = $this->getIndex()->search($query)->getResponse();
                $hits = $response->getData()['hits']['hits'] ?? [];
                foreach ($hits as $document) {
                    $documents[$document['_id']] = $document['_source'];
                }
            }

        } catch (Exception $e) {
            error_log($e);
            $created = $this->createIndexIfNotExists();
            if (!$created) {
                $this->getFlashBag()->add('warning', 'An error occurred during getting. ' . $e->getMessage());
            }
        }
        return $documents;
    }

//============================== CRUD CONTROLLER ==============================

    public function search(QueryBuilder $qb, SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields): void
    {
        if (!is_a($entityDto->getFqcn(), AttributableEntity::class, true)) {
            return;
        }
        $searchQuery = $searchDto->getQuery();
        if (empty($searchQuery)) {
            return;
        }
        if (is_numeric($searchQuery)) {
            //todo: need to be improved
            return;
        }

        /** @var Query $query */
        $query = $this->getSearchQuery($searchDto, $entityDto, $fields);
        if ($query === null) {
            return;
        }

        $query->setSize(0);
        $aggTerm = new Terms('count');
        $aggTerm->setField('id');
        $aggTerm->setSize(9999);
        $query->addAggregation($aggTerm);
        $resultSet = $this->getIndex()->search($query);
        $total = $resultSet->getTotalHits();
        if ($total > 200) {
            $this->getFlashBag()->add('warning', "The search delivers too many[$total] hits, you may have to repeat searching with a larger number of initials.");
            return;
        }
        $aggregation = $resultSet->getAggregation('count');
        $ids = array_column($aggregation['buckets'], 'key');
        if ($total > 0) {
            $expr = $qb->expr()->in('entity.id', $ids);
            $qb->andWhere($expr);
        }

//        $this->printQuery($query);die;
    }


    /**
     * @return IndexManager
     */
    protected function getIndexManager(): IndexManager
    {
        return $this->indexManager;
    }

    /**
     * @param Query $query
     */
    protected function printQuery(Query $query): void
    {
        $out = '<pre>';
        $out .= 'GET ' . $this->getIndex()->getName() . "/_search\n";
        $json = Json::encode($query->toArray());
        $out .= Json::prettyPrint($json, ['indent' => '  ']);
        $out .= '</pre><br>';
        echo $out;
    }

    abstract protected function getIndex(): Index;

    abstract protected function getQuery(Collection $entities): Query;

    abstract protected function getSearchQuery(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields): ?Query;

    abstract protected function createIndexIfNotExists(): bool;
}