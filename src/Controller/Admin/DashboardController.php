<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Entity\AttributeTab;
use App\Entity\AttributeType;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
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
        yield MenuItem::section('Product');
        yield MenuItem::linkToCrud('Products', 'fas fa-list', Product::class);
        yield MenuItem::linkToCrud('Category', 'fas fa-tree', Category::class);

        yield MenuItem::section('Attribute');
        yield MenuItem::linkToCrud('Attribute', 'fas fa-list', Attribute::class);
        yield MenuItem::linkToCrud('Attribute Tabs', 'fas fa-list', AttributeTab::class);
        yield MenuItem::linkToCrud('Attribute Types', 'fas fa-list', AttributeType::class);

        yield MenuItem::section('User');
        yield MenuItem::linkToCrud('User', 'fas fa-list', User::class);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addCssFile('/css/custom.css');
    }
}
