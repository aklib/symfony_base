<?php /** @noinspection PhpUnused */

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Entity\AttributeDefinition;
use App\Entity\AttributeOption;
use App\Entity\AttributeTab;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractDashboardController
{

    public const DATE_FORMAT_DEFAULT = 'y-MM-dd';
    public const DATETIME_FORMAT_DEFAULT = 'y-MM-dd hh:mm:ss';
    private EntityManagerInterface $em;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setDateFormat(self::DATE_FORMAT_DEFAULT)
            ->setDateTimeFormat(self::DATETIME_FORMAT_DEFAULT)
            ->setTimezone('Europe/Berlin')
            ;

    }

    /**
     * @Route("/admin", name="admin")
     * @noinspection SenselessProxyMethodInspection
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Dashboard')
            ->setFaviconPath('favicon.png')
            // by default, all backend URLs include a signature hash. If a user changes any
            // query parameter (to "hack" the backend) the signature won't match and EasyAdmin
            // triggers an error. If this causes any issue in your backend, call this method
            // to disable this feature and remove all URL signature checks
            ->disableUrlSignatures()
            // by default, all backend URLs are generated as absolute URLs. If you
            // need to generate relative URLs instead, call this method
            ->generateRelativeUrls();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        /** @var CategoryRepository $dao */
        $dao = $this->getEntityManager()->getRepository(Category::class);
        $roots = $dao->getRootNodes();
        $subItems = [];
        /** @var Category $root */
        foreach ($roots as $root) {
            $subItems[] = MenuItem::linkToCrud($root->getName(), 'fas fa-tree', Category::class);
            /** @var Category $child */
            foreach ($root->getChildren() as $child) {
                $subItems[] = MenuItem::linkToCrud($child->getName(), 'fas fa-list', Product::class)
                    ->setQueryParameter('category', $child->getId());
            }
            yield MenuItem::subMenu($root->getName(), 'fa fa-article')->setSubItems($subItems);

        }
        yield MenuItem::section('Attribute');
        yield MenuItem::linkToCrud('Attribute', 'fas fa-list', Attribute::class);
        yield MenuItem::linkToCrud('Attribute Tabs', 'fas fa-list', AttributeTab::class);
        yield MenuItem::linkToCrud('Attribute Types', 'fas fa-list', AttributeDefinition::class);
        yield MenuItem::linkToCrud('Attribute Options', 'fas fa-list', AttributeOption::class);

        yield MenuItem::section('User');
        yield MenuItem::linkToCrud('User', 'fas fa-list', User::class);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addCssFile('/css/custom.css');
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}
