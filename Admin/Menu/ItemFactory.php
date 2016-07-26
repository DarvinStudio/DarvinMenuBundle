<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\Menu;

use Darvin\AdminBundle\Menu\ItemFactoryInterface;
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\MenuBundle\Entity\Menu\Item;

/**
 * Admin menu item factory
 */
class ItemFactory implements ItemFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @var string[]
     */
    private $menuLabels;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter $adminRouter Admin router
     * @param string[]                              $menuLabels  Menu labels
     */
    public function __construct(AdminRouter $adminRouter, array $menuLabels)
    {
        $this->adminRouter = $adminRouter;
        $this->menuLabels = $menuLabels;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        foreach ($this->menuLabels as $label) {
            $items[] = $this->createItem($label);
        }

        return $items;
    }

    /**
     * @param string $menuLabel Menu label
     *
     * @return \Darvin\AdminBundle\Menu\Item
     */
    private function createItem($menuLabel)
    {
        return (new \Darvin\AdminBundle\Menu\Item('menu_'.$menuLabel))
            ->setIndexTitle('menu.'.$menuLabel)
            ->setIndexUrl($this->adminRouter->generate(null, Item::ITEM_CLASS, AdminRouter::TYPE_INDEX, [
                'menu' => $menuLabel,
            ]))
            ->setAssociatedObject(Item::ITEM_CLASS)
            ->setParentName('menu');
    }
}
