<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Switcher;

use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Repository\Menu\ItemRepository;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

/**
 * Menu switcher
 */
class MenuSwitcher implements MenuSwitcherInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var array
     */
    private $defaultMenuNames;

    /**
     * @var array
     */
    private $menuItems;

    /**
     * @var array
     */
    private $menusToEnable;

    /**
     * @var array
     */
    private $menusToDisable;

    /**
     * @param \Doctrine\ORM\EntityManager               $em               Entity manager
     * @param \Darvin\Utils\ORM\EntityResolverInterface $entityResolver   Entity resolver
     * @param array                                     $defaultMenuNames Default menu names
     */
    public function __construct(EntityManager $em, EntityResolverInterface $entityResolver, array $defaultMenuNames)
    {
        $this->em = $em;
        $this->entityResolver = $entityResolver;
        $this->defaultMenuNames = $defaultMenuNames;

        $this->menuItems = null;
        $this->menusToEnable = $this->menusToDisable = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultMenus($entity): array
    {
        foreach ($this->defaultMenuNames as $entityClass => $entityDefaultMenuNames) {
            if ($entity instanceof $entityClass) {
                return $entityDefaultMenuNames;
            }
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getMenusToEnable(): array
    {
        return $this->menusToEnable;
    }

    /**
     * {@inheritDoc}
     */
    public function getMenusToDisable(): array
    {
        return $this->menusToDisable;
    }

    /**
     * {@inheritDoc}
     */
    public function hasEnabledMenus($entity): bool
    {
        foreach (array_keys($this->getMenuItems()) as $menuName) {
            if ($this->isMenuEnabled($entity, $menuName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isMenuEnabled($entity, string $menuName): bool
    {
        $menuItems   = $this->getMenuItems();
        $entityClass = ClassUtils::getClass($entity);
        $entityIds   = $this->em->getClassMetadata($entityClass)->getIdentifierValues($entity);

        $entityId = reset($entityIds);

        foreach ([$entityClass, $this->entityResolver->reverseResolve($entityClass)] as $class) {
            if (isset($menuItems[$menuName][$class][$entityId])) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function toggleMenu($entity, string $menuName, bool $enable): void
    {
        $enable ? $this->enableMenu($entity, $menuName) : $this->disableMenu($entity, $menuName);
    }

    /**
     * @param object $entity   Entity
     * @param string $menuName Menu name
     */
    private function enableMenu(object $entity, string $menuName): void
    {
        if ($this->isMenuEnabled($entity, $menuName)) {
            return;
        }
        if (!isset($this->toEnable[$menuName])) {
            $this->menusToEnable[$menuName] = [];
        }

        $this->menusToEnable[$menuName][] = $entity;
    }

    /**
     * @param object $entity   Entity
     * @param string $menuName Menu name
     */
    private function disableMenu(object $entity, string $menuName): void
    {
        if (!$this->isMenuEnabled($entity, $menuName)) {
            return;
        }
        if (!isset($this->toDisable[$menuName])) {
            $this->menusToDisable[$menuName] = [];
        }

        $this->menusToDisable[$menuName][] = $entity;
    }

    /**
     * @return array
     */
    private function getMenuItems(): array
    {
        if (null === $this->menuItems) {
            $this->menuItems = $this->getMenuItemRepository()->getForMenuSwitcher();
        }

        return $this->menuItems;
    }

    /**
     * @return \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private function getMenuItemRepository(): ItemRepository
    {
        return $this->em->getRepository(Item::class);
    }
}
