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
    private $menuItems;

    /**
     * @var array
     */
    private $toEnable;

    /**
     * @var array
     */
    private $toDisable;

    /**
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        $this->menuItems = null;
        $this->toEnable = $this->toDisable = [];
    }

    /**
     * @param string $menuAlias Menu alias
     * @param object $entity    Entity
     */
    public function enable($menuAlias, $entity)
    {
        if ($this->isEnabled($menuAlias, $entity)) {
            return;
        }
        if (!isset($this->toEnable[$menuAlias])) {
            $this->toEnable[$menuAlias] = [];
        }

        $this->toEnable[$menuAlias][] = $entity;
    }

    /**
     * @param string $menuAlias Menu alias
     * @param object $entity    Entity
     */
    public function disable($menuAlias, $entity)
    {
        if (!$this->isEnabled($menuAlias, $entity)) {
            return;
        }
        if (!isset($this->toDisable[$menuAlias])) {
            $this->toDisable[$menuAlias] = [];
        }

        $this->toDisable[$menuAlias][] = $entity;
    }

    /**
     * @param string $menuAlias Menu alias
     * @param object $entity    Entity
     *
     * @return bool
     */
    public function isEnabled($menuAlias, $entity)
    {
        $menuItems   = $this->getMenuItems();
        $entityClass = ClassUtils::getClass($entity);
        $entityIds   = $this->em->getClassMetadata($entityClass)->getIdentifierValues($entity);

        return isset($menuItems[$menuAlias][$entityClass][reset($entityIds)]);
    }

    /**
     * @return array
     */
    public function getToEnable()
    {
        return $this->toEnable;
    }

    /**
     * @return array
     */
    public function getToDisable()
    {
        return $this->toDisable;
    }

    /**
     * @return array
     */
    private function getMenuItems()
    {
        if (null === $this->menuItems) {
            $this->menuItems = $this->getMenuItemRepository()->getForItemManager();
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
