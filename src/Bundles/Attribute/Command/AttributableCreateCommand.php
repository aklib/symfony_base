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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


class AttributableCreateCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
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
        $io = new ConsoleStyle($input, $output);

        $helper = $this->getHelper('question');
        $question = new Question('The name (camelcase) of the bundle[entity] : ');
//
        $entityName = $helper->ask($input, $output, $question);

        $entityName = "$entityName\\$entityName";
        $entityNameAttribute = $entityName . 'Attribute';
        $entityNameAttributeDef = $entityName . 'AttributeDef';
        $entityNameAttributeTab = $entityName . 'AttributeTab';
        $entityNameCategory = $entityName . 'Category';


//        $kernel = new MakerTestKernel('dev', true);
//        $kernel->registerBundles();
//        $kernel->boot();
//        /** @var MakeEntity $maker */
//        $maker = $kernel->getContainer()->get('maker.maker.make_entity');
//        $maker->generate($input, $io, $this->generator);

//        shell_exec('php bin/console -q make:entity ' . escapeshellarg($entityName) . "\n");
//        sleep(1);
//        shell_exec('php bin/console -q make:entity ' . escapeshellarg($entityNameAttribute) . "\n");
//        sleep(1);
//        shell_exec('php bin/console -q make:entity ' . escapeshellarg($entityNameAttributeDef) . "\n");
//        sleep(1);
//        shell_exec('php bin/console -q make:entity ' . escapeshellarg($entityNameAttributeTab) . "\n");
//        sleep(1);
//        shell_exec('php bin/console -q make:entity ' . escapeshellarg($entityNameCategory) . "\n");


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

    //============================== GETTERS ==============================

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }


}