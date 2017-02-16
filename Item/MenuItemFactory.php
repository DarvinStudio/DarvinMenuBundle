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

/**
 * Item from menu item entity factory
 */
class MenuItemFactory extends AbstractItemFactory
{
    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return Item::class === $class;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     *
     * @return string
     */
    protected function getLabel($menuItem)
    {
        // TODO: Implement getLabel() method.
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     *
     * @return string
     */
    protected function getUri($menuItem)
    {
        // TODO: Implement getUri() method.
    }
}
