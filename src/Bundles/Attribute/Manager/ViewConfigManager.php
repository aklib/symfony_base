<?php
/**
 * Class ViewConfigManager
 * @package App\Bundles\Attribute\Manager
 *
 * since: 18.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Manager;

use App\Entity\User;
use App\Entity\UserViewConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ViewConfigManager implements EventSubscriberInterface
{
    private $entityFqcn;
    private EntityManagerInterface $em;
    private Security $security;
    private RequestStack $requestStack;
    private ?UserViewConfig $currentUserViewConfig = null;
    private bool $doFlash = false;

    public function __construct(RequestStack $requestStack, Security $security, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->em = $em;
    }

    /**
     * @return Collection<UserViewConfig>
     */
    protected function getViewConfigs(): Collection
    {
        $userViewConfigs = new ArrayCollection();
        $user = $this->getUser();
        if ($user instanceof User) {
            $dao = $this->getEntityManager()->getRepository(UserViewConfig::class);
            $found = $dao->findBy([
                'user'       => $this->getUser(),
                'entityFqcn' => $this->getEntityFqcn()
            ]);
            if (count($found) > 0) {
                foreach ($found as $entity) {
                    $userViewConfigs->add($entity);
                }
            }
        }
        return $userViewConfigs;
    }

    public function getCurrentViewConfig(string $pageName = 'index'): ?UserViewConfig
    {
        if ($this->currentUserViewConfig !== null) {
            return $this->currentUserViewConfig;
        }

        $userViewConfigs = $this->getViewConfigs();
        if ($userViewConfigs->isEmpty()) {
            return null;
        }
        $viewConfigId = (int)$this->getRequest()->query->get('viewConfig');
        if ($viewConfigId > 0) {
            // switch config
            foreach ($userViewConfigs as $userViewConfig) {
                $userViewConfig->setCurrent($viewConfigId === $userViewConfig->getId());
                $this->getEntityManager()->persist($userViewConfig);
            }
            // can't do flush directly, it destroys loaded entities
            $this->doFlash = true;
        }

        $results = $userViewConfigs->filter(static function (UserViewConfig $viewConfig) use ($viewConfigId) {
            if ($viewConfig->isCurrent()) {
                return $viewConfig;
            }
            return null;
        });
        $this->currentUserViewConfig = $results->isEmpty() ? null : $results->first();
        return $this->currentUserViewConfig;
    }

    public function createViewConfig(string $name = null): UserViewConfig
    {
        if ($name === null) {
            $name = date('Y-m-d H:i');
        }
        $currentConfig = new UserViewConfig();
        $currentConfig->setUser($this->getUser());
        $currentConfig->setCurrent(true);
        $currentConfig->setName($name);
        $currentConfig->setEntityFqcn($this->getEntityFqcn());
        return $currentConfig;
    }

    /**
     * @return mixed
     */
    public function getEntityFqcn()
    {
        return $this->entityFqcn;
    }

    /**
     * @param mixed $entityFqcn
     * @return ViewConfigManager
     */
    public function setEntityFqcn($entityFqcn): ViewConfigManager
    {
        $this->entityFqcn = $entityFqcn;
        return $this;
    }


    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @return Request
     */
    protected function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return UserInterface
     */
    protected function getUser(): UserInterface
    {
        return $this->security->getUser();

    }

    //================== EVENT ==================
    public static function getSubscribedEvents(): array
    {
        return [
            AfterCrudActionEvent::class => ['doFlush']
        ];
    }

    public function doFlush(AfterCrudActionEvent $event): void
    {
        if ($this->doFlash) {
            $this->getEntityManager()->flush();
        }
    }
}