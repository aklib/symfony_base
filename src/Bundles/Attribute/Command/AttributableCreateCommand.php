<?php /** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

/**
 * Class PopulateAttributeValues
 * @package App\Bundles\Attribute\Command
 *
 * since: 01.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Command;

use App\Bundles\Attribute\Manager\AbstractElasticaAttributeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\Maker\MakeEntity;
use Symfony\Bundle\MakerBundle\Test\MakerTestKernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;


class AttributableCreateCommand extends Command
{
    private EntityManagerInterface $em;

    /** @noinspection PhpOptionalBeforeRequiredParametersInspection */
    public function __construct(string $name = null, EntityManagerInterface $em)
    {
        parent::__construct($name);
        $this->em = $em;
    }

    protected static $defaultName = 'attributable:create';

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp("This command allows you to populate attribute values from database table 'AttributeValue' into configured elasticsearch indexes")
            ->setDescription('Populates search indexes from database backup table')
//            ->addOption('do-reset-ask', null, InputOption::VALUE_NONE, 'Ask if the index needs to be reset')
//            ->addOption('do-reset', null, InputOption::VALUE_OPTIONAL, 'The index needs to be reset')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Creating an attributable object',
            '============',
            '',
        ]);

        $helper = $this->getHelper('question');
        $question = new Question('The name (camelcase) of the bundle[entity] : ');
//
        $entityName = $helper->ask($input, $output, $question);
        $entityName = "Attributable\\$entityName";

        $kernel = new MakerTestKernel('dev', true);
        $kernel->boot();
        /** @var MakeEntity $maker */
//        $maker = $kernel->getContainer()->get('maker.maker.make_entity');
//        $maker->generate($input,);
        $out = shell_exec('php bin/console -n -q make:entity ' . escapeshellarg($entityName) . "\n");

        $out .= shell_exec('php bin/console -n -q make:entity ' . escapeshellarg($entityName) . "\n");


        $output->writeln($out);


        return Command::SUCCESS;
        //================= RESET INDEX? =================
//        if ($this->resetIndex($this->getManagerNested(), $input, $output)) {
//            $output->writeln(sprintf("\tIndex <info>%s</> has been <info>reset</>", $this->getManagerNested()->getIndex()->getName()));
//        } else {
//            $output->writeln(sprintf("\tIndex resetting <comment>%s</> was <comment>canceled</>", $this->getManagerNested()->getIndex()->getName()));
//        }
//
//        if ($this->resetIndex($this->getManagerParentChild(), $input, $output)) {
//            $output->writeln(sprintf("\tIndex <info>%s</> has been <info>reset</>", $this->getManagerParentChild()->getIndex()->getName()));
//        } else {
//            $output->writeln(sprintf("\tIndex resetting <comment>%s</> was <comment>canceled</>", $this->getManagerParentChild()->getIndex()->getName()));
//        }
//        //================= CREATE INDEXES IF NOT EXISTS =================
//
//
//        return Command::SUCCESS;
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


    //============================== GETTERS ==============================

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }


}