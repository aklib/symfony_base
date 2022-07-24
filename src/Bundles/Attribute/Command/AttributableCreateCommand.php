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
use ReflectionClass;
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
        $entityShortName = $helper->ask($input, $output, $question);

        $templates = [
            'Entity',
            'Attribute',
            'AttributeTab',
            'Category',
        ];

        $entityName = "$entityShortName\\$entityShortName";
        $entityShortNameLower = lcfirst($entityShortName);

        $templateBase = __DIR__ . '/Template';

        shell_exec('php bin/console -q -n --env=dev cache:clear');

        foreach ($templates as $template) {
            if ($template !== 'Entity') {
                $class = $entityName . $template;
            } else {
                $class = $entityName;
            }
            shell_exec('php bin/console -q -n --env=dev make:entity ' . escapeshellarg($class) . "\n");

            // get template
            $path = "$templateBase/$template.php.dist";
            if (!file_exists($path)) {
                $output->writeln(sprintf("Class not found: %s", $path));
                continue;
            }
            //entity class
            $refClass = new ReflectionClass("App\\Entity\\$class");
            $output->writeln(sprintf("File path: %s", $refClass->getFileName()));

            $entityContent = file_get_contents($path);
            $entityContent = str_replace(['Xxxxx', 'xxxxx'], [$entityShortName, $entityShortNameLower], $entityContent);
            // clear class content
            file_put_contents($refClass->getFileName(), '');
            $result = file_put_contents($refClass->getFileName(), $entityContent, FILE_APPEND);
            sleep(1);

            $output->writeln("Entity has been created: App\\Entity\\$class with result[$result]");

        }

        $output->writeln('These are the next steps:');
        $output->writeln('Please refresh the your admin page. The Symfony cache will be created.');
        $output->writeln('Create database tables: php bin/console  doctrine:schema:update --force');
        $output->writeln(sprintf("Create directory src/Controller/Admin/%s", $entityShortName));
        $output->writeln(sprintf("Create crud controllers for all entities starts with '%s': php bin/console make:admin:crud", $entityShortName));
        $output->writeln(sprintf("Replace in %sCrudController: 'extends AbstractCrudController' with 'extends AbstractAttributableEntityController'", $entityShortName));
        $output->writeln(sprintf("Replace in %sCategoryCrudController: 'extends AbstractCrudController' with 'extends AbstractCategoryCrudController'", $entityShortName));
        $output->writeln("Replace in other controllers: 'extends AbstractCrudController' with 'extends AbstractAppGrudController'");

        $output->writeln(sprintf("Create a menu for  %ss in DashboardController::configureMenuItems()", $entityShortName));
        $output->writeln(sprintf('Enjoy your new functionality. Start with creating of %sCategory root named %s', $entityShortName, $entityShortName));
        $output->writeln('Bye-bye!');
        return Command::SUCCESS;
    }

    //============================== GETTERS ==============================

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }


}