<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item;

use Darvin\MenuBundle\Entity\Menu\Item;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

/**
 * Menu item manager
 */
class MenuItemManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $items;

    /**
     * @var array
     */
    private $scheduledForAdding;

    /**
     * @var array
     */
    private $scheduledForRemoval;

    /**
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        $this->items = null;
        $this->scheduledForAdding = $this->scheduledForRemoval = [];
    }

    /**
     * @param string $menuAlias Menu alias
     * @param object $entity    Entity
     */
    public function scheduleForAdding($menuAlias, $entity)
    {
        if ($this->exists($menuAlias, $entity)) {
            return;
        }
        if (!isset($this->scheduledForAdding[$menuAlias])) {
            $this->scheduledForAdding[$menuAlias] = [];
        }

        $this->scheduledForAdding[$menuAlias][] = $entity;
    }

    /**
     * @param string $menuAlias Menu alias
     * @param object $entity    Entity
     */
    public function scheduleForRemoval($menuAlias, $entity)
    {
        if (!$this->exists($menuAlias, $entity)) {
            return;
        }
        if (!isset($this->scheduledForRemoval[$menuAlias])) {
            $this->scheduledForRemoval[$menuAlias] = [];
        }

        $this->scheduledForRemoval[$menuAlias][] = $entity;
    }

    /**
     * @return array
     */
    public function getScheduledForAdding()
    {
        return $this->scheduledForAdding;
    }

    /**
     * @return array
     */
    public function getScheduledForRemoval()
    {
        return $this->scheduledForRemoval;
    }

    /**
     * @param string $menuAlias Menu alias
     * @param object $entity    Entity
     *
     * @return bool
     */
    public function exists($menuAlias, $entity)
    {
        $items       = $this->getItems();
        $entityClass = ClassUtils::getClass($entity);
        $entityIds   = $this->em->getClassMetadata($entityClass)->getIdentifierValues($entity);

        return isset($items[$menuAlias][$entityClass][reset($entityIds)]);
    }

    /**
     * @return array
     */
    private function getItems()
    {
        if (null === $this->items) {
            $this->items = $this->getMenuItemRepository()->getForItemManager();
        }

        return $this->items;
    }

    /**
     * @return \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private function getMenuItemRepository()
    {
        return $this->em->getRepository(Item::class);
    }
}
