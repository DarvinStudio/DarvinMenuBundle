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
    public function getItems(): iterable
    {
        $items = [];

        $filterFormTypeName = $this->metadataManager->getMetadata(Item::class)->getFilterFormTypeName();

        foreach ($this->menuConfig->getMenus() as $position => $menu) {
            $items[] = $this->createItem($menu, $filterFormTypeName, $position);
        }

        return $items;
    }

    /**
     * @param \Darvin\MenuBundle\Configuration\Menu $menu               Menu
     * @param string                                $filterFormTypeName Filter form type name
     * @param int                                   $position           Position
     *
     * @return \Darvin\AdminBundle\Menu\Item
     */
    private function createItem(Menu $menu, $filterFormTypeName, $position)
    {
        $routeParams = [
            $filterFormTypeName => [
                'menu' => $menu->getAlias(),
            ],
        ];

        return (new \Darvin\AdminBundle\Menu\Item('menu_'.$menu->getAlias()))
            ->setIndexTitle($menu->getTitle())
            ->setIndexUrl($this->adminRouter->generate(null, Item::class, AdminRouter::TYPE_INDEX, $routeParams))
            ->setNewUrl($this->adminRouter->generate(null, Item::class, AdminRouter::TYPE_NEW, $routeParams))
            ->setNewTitle($this->metadataManager->getMetadata(Item::class)->getBaseTranslationPrefix().'action.new.link')
            ->setMainIcon($menu->getIcon())
            ->setPosition($position)
            ->setAssociatedObject(Item::class)
            ->setParentName('menu');
    }
}
