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
    private $defaultMenuAliases;

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
     * @param \Doctrine\ORM\EntityManager               $em                 Entity manager
     * @param \Darvin\Utils\ORM\EntityResolverInterface $entityResolver     Entity resolver
     * @param array                                     $defaultMenuAliases Default menu aliases
     */
    public function __construct(EntityManager $em, EntityResolverInterface $entityResolver, array $defaultMenuAliases)
    {
        $this->em = $em;
        $this->entityResolver = $entityResolver;
        $this->defaultMenuAliases = $defaultMenuAliases;

        $this->menuItems = null;
        $this->menusToEnable = $this->menusToDisable = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultMenus($entity): array
    {
        foreach ($this->defaultMenuAliases as $entityClass => $entityDefaultMenuAliases) {
            if ($entity instanceof $entityClass) {
                return $entityDefaultMenuAliases;
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
        foreach (array_keys($this->getMenuItems()) as $menuAlias) {
            if ($this->isMenuEnabled($entity, $menuAlias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isMenuEnabled($entity, string $menuAlias): bool
    {
        $menuItems   = $this->getMenuItems();
        $entityClass = ClassUtils::getClass($entity);
        $entityIds   = $this->em->getClassMetadata($entityClass)->getIdentifierValues($entity);

        $entityId = reset($entityIds);

        foreach ([$entityClass, $this->entityResolver->reverseResolve($entityClass)] as $class) {
            if (isset($menuItems[$menuAlias][$class][$entityId])) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function toggleMenu($entity, string $menuAlias, bool $enable): void
    {
        $enable ? $this->enableMenu($entity, $menuAlias) : $this->disableMenu($entity, $menuAlias);
    }

    /**
     * @param object $entity    Entity
     * @param string $menuAlias Menu alias
     */
    private function enableMenu(object $entity, string $menuAlias): void
    {
        if ($this->isMenuEnabled($entity, $menuAlias)) {
            return;
        }
        if (!isset($this->toEnable[$menuAlias])) {
            $this->menusToEnable[$menuAlias] = [];
        }

        $this->menusToEnable[$menuAlias][] = $entity;
    }

    /**
     * @param object $entity    Entity
     * @param string $menuAlias Menu alias
     */
    private function disableMenu(object $entity, string $menuAlias): void
    {
        if (!$this->isMenuEnabled($entity, $menuAlias)) {
            return;
        }
        if (!isset($this->toDisable[$menuAlias])) {
            $this->menusToDisable[$menuAlias] = [];
        }

        $this->menusToDisable[$menuAlias][] = $entity;
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
