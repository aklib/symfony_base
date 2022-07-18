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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ViewConfigManager
{
    private Request $request;
    private UserInterface $user;
    private $entityFqcn;
    private EntityManagerInterface $em;

    public function __construct(RequestStack $requestStack, Security $security, EntityManagerInterface $em)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->user = $security->getUser();
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

    public function getCurrentViewConfig(): ?UserViewConfig
    {
        $viewConfigId = (int)$this->getRequest()->query->get('viewConfig');

        $userViewConfigs = $this->getViewConfigs();
        if ($userViewConfigs->isEmpty()) {
            return null;
        }
        $results = $userViewConfigs->filter(static function (UserViewConfig $viewConfig) use ($viewConfigId) {
            if ($viewConfigId === 0 && $viewConfig->isCurrent()) {
                return $viewConfig;
            }
            if ($viewConfigId === $viewConfig->getId()) {
                return $viewConfig;
            }
            return null;
        });
        if ($viewConfigId !== 0) {
            // switch view
            /** @var  UserViewConfig $userViewConfig */
            foreach ($userViewConfigs as $userViewConfig) {
                $userViewConfig->setCurrent($viewConfigId === $userViewConfig->getId());
                $this->getEntityManager()->persist($userViewConfig);
            }
            $this->getEntityManager()->flush();
        } elseif ($results->count() > 1) {
            /** @var UserViewConfig $result */
            foreach ($results as $i => $result) {
                $result->setCurrent($i === 0);
                $this->getEntityManager()->persist($result);
            }
            $this->getEntityManager()->flush();
        }
        return $results->isEmpty() ? null : $results->first();
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
        return $this->request;
    }

    /**
     * @return UserInterface
     */
    protected function getUser(): UserInterface
    {
        return $this->user;

    }


}