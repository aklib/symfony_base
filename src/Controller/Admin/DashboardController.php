<?php /** @noinspection PhpUnused */

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Entity\AttributeDefinition;
use App\Entity\AttributeOption;
use App\Entity\AttributeTab;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

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
            ->setTimezone('Europe/Berlin');

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
            $item = MenuItem::subMenu($root->getName(), 'fa fa-article')->setSubItems($subItems);
//            $item->getAsDto()->('expanded');
            yield $item;
        }
        yield MenuItem::section('Attribute')->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Attribute', 'fas fa-list', Attribute::class)->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Attribute Tabs', 'fas fa-list', AttributeTab::class)->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Attribute Definitions', 'fas fa-list', AttributeDefinition::class)->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Attribute Options', 'fas fa-list', AttributeOption::class)->setPermission('ROLE_ADMIN');

        yield MenuItem::section('User')->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('User', 'fas fa-list', User::class)->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('UserProfile', 'fas fa-list', UserProfile::class)->setPermission('ROLE_ADMIN');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $userMenuItems = [];
        $img = null;
        if ($user instanceof User && $user->getUserProfile() !== null) {
            $img = $user->getUserProfile()->avatar;
            $userMenuItems[] = MenuItem::linkToCrud('Profile', 'fa-id-card', UserProfile::class)
                ->setAction(Crud::PAGE_DETAIL)
                ->setEntityId($user->getUserProfile()->getId());
        }
        $userMenuItems[] = MenuItem::section();
        $userMenuItems[] = MenuItem::linkToLogout('__ea__user.sign_out', 'fa-sign-out');

        if ($this->isGranted(Permission::EA_EXIT_IMPERSONATION)) {
            $userMenuItems[] =
                MenuItem::linkToExitImpersonation(
                    '__ea__user.exit_impersonation',
                    'fa-user-lock'
                );
        }

        $menu = UserMenu::new()
            ->displayUserName()
            ->displayUserAvatar(false)
            ->setName(method_exists($user, '__toString') ? (string)$user : $user->getUserIdentifier())
            ->setMenuItems($userMenuItems);

        if (!empty($img)) {
            $base = $this->getParameter('web_base_path');
            $uploadPath = $this->getParameter('upload_image_path');
            $path = "$base/$uploadPath/avatar/$img";

            if (file_exists($path)) {
                $menu->setAvatarUrl("/$uploadPath/avatar/$img");
            }
        }

        return $menu;
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
