<?php /** @noinspection PhpUnused */

/**
 * Class PopulateAttributeValues
 * @package App\Bundles\Attribute\Command
 *
 * since: 01.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Command;

use App\Bundles\Attribute\Manager\AbstractElasticaAttributeManager;
use App\Bundles\Attribute\Manager\AttributeManagerDatabase;
use App\Bundles\Attribute\Manager\AttributeManagerNested;
use App\Bundles\Attribute\Manager\AttributeManagerParentChild;
use App\Entity\AttributeValue;
use App\Entity\Extension\Attributable\AttributableEntity;
use App\Repository\AttributeValueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateAttributeCommand extends Command
{
    private AttributeManagerDatabase $managerDatabase;
    private AttributeManagerNested $managerNested;
    private AttributeManagerParentChild $managerParentChild;
    private EntityManagerInterface $em;
    private ArrayCollection $attributeValueCollection;

    public function __construct(AttributeManagerDatabase $managerDatabase, AttributeManagerNested $managerNested, AttributeManagerParentChild $managerParentChild, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->managerDatabase = $managerDatabase;
        $this->managerNested = $managerNested;
        $this->managerParentChild = $managerParentChild;
        $this->em = $em;
        $this->attributeValueCollection = new ArrayCollection();
    }

    protected static $defaultName = 'attributable:populate';

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp("This command allows you to populate attribute values from database table 'AttributeValue' into configured elasticsearch indexes")
            ->setDescription('Populates search indexes from database backup table')
            ->addOption('do-reset-ask', null, InputOption::VALUE_NONE, 'Ask if the index needs to be reset')
            ->addOption('do-reset', null, InputOption::VALUE_OPTIONAL, 'The index needs to be reset');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Populating of attribute values into elasticsearch',
            '============',
            '',
        ]);
        $this->getManagerNested()->setDoSynchronize(false);
        $this->getManagerParentChild()->setDoSynchronize(false);

        $io = new SymfonyStyle($input, $output);

        //================= RESET INDEX? =================
        if ($this->resetIndex($this->getManagerNested(), $input, $output)) {
            $output->writeln(sprintf("\tIndex <info>%s</> has been <info>reset</>", $this->getManagerNested()->getIndex()->getName()));
        } else {
            $output->writeln(sprintf("\tIndex resetting <comment>%s</> was <comment>canceled</>", $this->getManagerNested()->getIndex()->getName()));
        }

        if ($this->resetIndex($this->getManagerParentChild(), $input, $output)) {
            $output->writeln(sprintf("\tIndex <info>%s</> has been <info>reset</>", $this->getManagerParentChild()->getIndex()->getName()));
        } else {
            $output->writeln(sprintf("\tIndex resetting <comment>%s</> was <comment>canceled</>", $this->getManagerParentChild()->getIndex()->getName()));
        }
        //================= CREATE INDEXES IF NOT EXISTS =================

        if ($this->getManagerParentChild()->createIndexIfNotExists()) {
            $output->writeln(sprintf("\tindex %s has been created", $this->getManagerParentChild()->getIndex()->getName()));
        }

        $maxResult = 100;
        $page = 1;
        /** @var AttributeValueRepository $dao */
        $dao = $this->getEntityManager()->getRepository(AttributeValue::class);
        $total = $dao->count([]);
        $output->writeln('Total entries: ' . $total);

        foreach ($this->getScopes() as $scope => $fqcn) {
            $output->writeln("\t<info>populate: $scope [$fqcn]</>");

            $dql = sprintf("SELECT count(doc.id) FROM %s doc WHERE doc.scope = '%s'", AttributeValue::class, $scope);
            try {
                $total = (int)$this->getEntityManager()->createQuery($dql)->getSingleScalarResult();
            } catch (NoResultException|NonUniqueResultException $e) {
                $total = 0;
            }
            if ($total === 0) {
                $output->writeln("\t<comment>no entries found</>");
                continue;
            }
            $io->progressStart($total);
            do {
                $dql = sprintf("SELECT entity, doc FROM %s entity, %s doc WHERE entity.id = doc.attributableId and doc.scope = '%s'", $fqcn, AttributeValue::class, $scope);
                $firstResult = ($page - 1) * $maxResult;
                $query = $this->getEntityManager()->createQuery($dql)
                    ->setFirstResult($firstResult)
                    ->setMaxResults($maxResult);
                $result = $query->execute();
                if (empty($result)) {
                    break;
                }
                /** @var AttributableEntity $entity */
                foreach ($result as $entity) {
                    if ($entity instanceof AttributableEntity) {
                        $this->getManagerNested()->addEntity($entity);
                        $this->getManagerParentChild()->addEntity($entity);
                    } elseif ($entity instanceof AttributeValue) {
                        $this->attributeValueCollection->add($entity);
                    }
                }
                foreach ($result as $entity) {
                    if ($entity instanceof AttributableEntity) {
                        $attributeValue = $this->getAttributeValue($entity, $scope);
                        if ($attributeValue instanceof AttributeValue) {
                            $entity->setAttributeValues($attributeValue->getDocData());
                        }
                    }
                }
                $this->getManagerNested()->flush();
                $this->getManagerParentChild()->flush();
                $page++;
                $io->progressAdvance($maxResult);
            } while (true);
            $io->progressFinish();
        }

        return Command::SUCCESS;
    }

    protected function resetIndex(AbstractElasticaAttributeManager $manager, InputInterface $input, OutputInterface $output): bool
    {
        $result = false;
        $doReset = $input->getOption('do-reset');
        if ($doReset === null) {
            $doReset = true;
        }
        if ($input->getOption('do-reset-ask') === null) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                sprintf("Do reset '%s' index before populating? y/n", $manager->getIndex()->getName()),
                false,
                '/^(y|j)/i'
            );

            if ($helper->ask($input, $output, $question)) {
                $doReset = true;
            }
        }
        if ($doReset) {
            $manager->getIndex()->delete();
            $result = $manager->createIndexIfNotExists();
        }
        return $result;
    }

    public function getAttributeValue(AttributableEntity $entity, string $scope): ?AttributeValue
    {
        $result = $this->attributeValueCollection->filter(static function (AttributeValue $attributeValue) use ($entity, $scope) {
            // filter by unique key
            if ($scope === $attributeValue->getScope() && $attributeValue->getAttributableId() === $entity->getId()) {
                return $attributeValue;
            }
            return null;
        });
        if ($result->isEmpty()) {
            return null;
        }
        return $result->first();
    }

    //============================== HELP METHODS ==============================

    private function getScopes(): array
    {
        $scopes = [];
        $allMetadata = $this->getEntityManager()->getMetadataFactory()->getAllMetadata();
        /** @var ClassMetadata $metadata */
        foreach ($allMetadata as $metadata) {
            if (is_a($metadata->getName(), AttributableEntity::class, true)) {
                $fqcn = $metadata->getName();
                $scopes[$this->getManagerDatabase()->getScopeFromFqcn($fqcn)] = $fqcn;
            }
        }
        return $scopes;
    }


    //============================== GETTERS ==============================

    /**
     * @return AttributeManagerDatabase
     */
    public function getManagerDatabase(): AttributeManagerDatabase
    {
        return $this->managerDatabase;
    }

    /**
     * @return AttributeManagerNested
     */
    public function getManagerNested(): AttributeManagerNested
    {
        return $this->managerNested;
    }

    /**
     * @return AttributeManagerParentChild
     */
    public function getManagerParentChild(): AttributeManagerParentChild
    {
        return $this->managerParentChild;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }


}