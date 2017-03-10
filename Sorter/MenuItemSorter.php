<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Sorter;

use Darvin\MenuBundle\Entity\Menu\Item;

/**
 * Menu item sorter
 */
class MenuItemSorter
{
    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item[] $menuItems Menu items
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    public function sort(array $menuItems)
    {
        if (empty($menuItems)) {
            return [];
        }

        $children = [];

        foreach ($menuItems as $menuItem) {
            if (null === $menuItem->getParent()) {
                continue;
            }

            $parentId = $menuItem->getParent()->getId();

            if (!isset($children[$parentId])) {
                $children[$parentId] = [];
            }

            $children[$parentId][] = $menuItem;
        }

        $sorted = [];

        foreach ($menuItems as $menuItem) {
            $this->addMenuItem($sorted, $menuItem, $children);
        }

        return $sorted;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item[] $sorted   Sorted menu items
     * @param \Darvin\MenuBundle\Entity\Menu\Item   $menuItem Menu item to add
     * @param array                                 $children Child menu items
     */
    private function addMenuItem(array &$sorted, Item $menuItem, array $children)
    {
        if (isset($sorted[$menuItem->getId()])) {
            return;
        }

        $sorted[$menuItem->getId()] = $menuItem;

        if (!isset($children[$menuItem->getId()])) {
            return;
        }
        foreach ($children[$menuItem->getId()] as $child) {
            $this->addMenuItem($sorted, $child, $children);
        }
    }
}
