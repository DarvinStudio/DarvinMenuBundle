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
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\MenuBundle\Configuration\Menu;
use Darvin\MenuBundle\Configuration\MenuConfiguration;
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
     * @var \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private $menuConfig;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter              $adminRouter     Admin router
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration $menuConfig      Menu configuration
     * @param \Darvin\AdminBundle\Metadata\MetadataManager       $metadataManager Metadata manager
     */
    public function __construct(AdminRouter $adminRouter, MenuConfiguration $menuConfig, MetadataManager $metadataManager)
    {
        $this->adminRouter = $adminRouter;
        $this->menuConfig = $menuConfig;
        $this->metadataManager = $metadataManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        $filterFormTypeName = $this->metadataManager->getMetadata(Item::ITEM_CLASS)->getFilterFormTypeName();

        foreach ($this->menuConfig->getMenus() as $menu) {
            $items[] = $this->createItem($menu, $filterFormTypeName);
        }

        return $items;
    }

    /**
     * @param \Darvin\MenuBundle\Configuration\Menu $menu               Menu
     * @param string                                $filterFormTypeName Filter form type name
     *
     * @return \Darvin\AdminBundle\Menu\Item
     */
    private function createItem(Menu $menu, $filterFormTypeName)
    {
        return (new \Darvin\AdminBundle\Menu\Item('menu_'.$menu->getLabel()))
            ->setIndexTitle($menu->getTitle())
            ->setIndexUrl($this->adminRouter->generate(null, Item::ITEM_CLASS, AdminRouter::TYPE_INDEX, [
                $filterFormTypeName.'[menu]' => $menu->getLabel(),
            ]))
            ->setAssociatedObject(Item::ITEM_CLASS)
            ->setParentName('menu');
    }
}
