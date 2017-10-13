<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Switcher;

use Darvin\MenuBundle\Entity\Menu\Item;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

/**
 * Menu switcher
 */
class MenuSwitcher
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

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
     * @param \Doctrine\ORM\EntityManager $em                 Entity manager
     * @param array                       $defaultMenuAliases Default menu aliases
     */
    public function __construct(EntityManager $em, array $defaultMenuAliases)
    {
        $this->em = $em;
        $this->defaultMenuAliases = $defaultMenuAliases;

        $this->menuItems = null;
        $this->menusToEnable = $this->menusToDisable = [];
    }

    /**
     * @param object $entity Entity
     *
     * @return string[]
     */
    public function getDefaultMenus($entity)
    {
        foreach ($this->defaultMenuAliases as $entityClass => $entityDefaultMenuAliases) {
            if ($entity instanceof $entityClass) {
                return $entityDefaultMenuAliases;
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function getMenusToEnable()
    {
        return $this->menusToEnable;
    }

    /**
     * @return array
     */
    public function getMenusToDisable()
    {
        return $this->menusToDisable;
    }

    /**
     * @param object $entity Entity
     *
     * @return bool
     */
    public function hasEnabledMenus($entity)
    {
        foreach (array_keys($this->getMenuItems()) as $menuAlias) {
            if ($this->isMenuEnabled($entity, $menuAlias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param object $entity    Entity
     * @param string $menuAlias Menu alias
     *
     * @return bool
     */
    public function isMenuEnabled($entity, $menuAlias)
    {
        $menuItems   = $this->getMenuItems();
        $entityClass = ClassUtils::getClass($entity);
        $entityIds   = $this->em->getClassMetadata($entityClass)->getIdentifierValues($entity);

        return isset($menuItems[$menuAlias][$entityClass][reset($entityIds)]);
    }

    /**
     * @param object $entity    Entity
     * @param string $menuAlias Menu alias
     * @param bool   $enable    Whether to enable menu
     */
    public function toggleMenu($entity, $menuAlias, $enable)
    {
        $enable ? $this->enableMenu($entity, $menuAlias) : $this->disableMenu($entity, $menuAlias);
    }

    /**
     * @param object $entity    Entity
     * @param string $menuAlias Menu alias
     */
    private function enableMenu($entity, $menuAlias)
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
    private function disableMenu($entity, $menuAlias)
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
    private function getMenuItems()
    {
        if (null === $this->menuItems) {
            $this->menuItems = $this->getMenuItemRepository()->getForMenuSwitcher();
        }

        return $this->menuItems;
    }

    /**
     * @return \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private function getMenuItemRepository()
    {
        return $this->em->getRepository(Item::class);
    }
}
