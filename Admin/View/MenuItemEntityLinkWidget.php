<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\View;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\AbstractWidget;
use Darvin\MenuBundle\Entity\Menu\Item;

/**
 * Menu item entity link admin view widget
 */
class MenuItemEntityLinkWidget extends AbstractWidget
{
    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     * @param array                               $options  Options
     * @param string                              $property Property name
     *
     * @return string
     */
    protected function createContent($menuItem, array $options, $property)
    {
        if (null === $menuItem->getSlugMapItem()) {
            return null;
        }

        return $menuItem->getSlugMapItem()->getObject();
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedEntityClasses()
    {
        return [
            Item::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return [
            Permission::VIEW,
        ];
    }
}
