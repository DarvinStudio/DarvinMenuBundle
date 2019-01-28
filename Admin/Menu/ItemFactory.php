<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\Menu;

use Darvin\AdminBundle\Menu\ItemFactoryInterface;
use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\MenuBundle\Configuration\Menu;
use Darvin\MenuBundle\Configuration\MenuConfiguration;
use Darvin\MenuBundle\Entity\Menu\Item;

/**
 * Admin menu item factory
 */
class ItemFactory implements ItemFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private $menuConfig;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface             $adminRouter     Admin router
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration         $menuConfig      Menu configuration
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager Metadata manager
     */
    public function __construct(AdminRouterInterface $adminRouter, MenuConfiguration $menuConfig, AdminMetadataManagerInterface $metadataManager)
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
        $filterFormTypeName = $this->metadataManager->getMetadata(Item::class)->getFilterFormTypeName();

        foreach (array_values($this->menuConfig->getMenus()) as $position => $menu) {
            yield $this->createItem($menu, $filterFormTypeName, $position);
        }
    }

    /**
     * @param \Darvin\MenuBundle\Configuration\Menu $menu               Menu
     * @param string                                $filterFormTypeName Filter form type name
     * @param int                                   $position           Position
     *
     * @return \Darvin\AdminBundle\Menu\Item
     */
    private function createItem(Menu $menu, string $filterFormTypeName, int $position): \Darvin\AdminBundle\Menu\Item
    {
        $routeParams = [
            $filterFormTypeName => [
                'menu' => $menu->getAlias(),
            ],
        ];

        return (new \Darvin\AdminBundle\Menu\Item(sprintf('menu_%s', $menu->getAlias())))
            ->setIndexTitle($menu->getTitle())
            ->setIndexUrl($this->adminRouter->generate(null, Item::class, AdminRouterInterface::TYPE_INDEX, $routeParams))
            ->setNewUrl($this->adminRouter->generate(null, Item::class, AdminRouterInterface::TYPE_NEW, $routeParams))
            ->setNewTitle(sprintf('%saction.new.link', $this->metadataManager->getMetadata(Item::class)->getBaseTranslationPrefix()))
            ->setMainIcon($menu->getIcon())
            ->setPosition($position)
            ->setAssociatedObject(Item::class)
            ->setParentName('menu');
    }
}
