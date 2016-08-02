<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Builder;

use Darvin\MenuBundle\Item\ItemFactoryInterface;
use Darvin\MenuBundle\Repository\Menu\ItemRepository;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Knp\Menu\FactoryInterface;

/**
 * Builder
 */
class Builder
{
    const BUILD_METHOD = 'buildMenu';

    /**
     * @var \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    private $customObjectLoader;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $genericItemFactory;

    /**
     * @var \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private $menuItemRepository;

    /**
     * @var string
     */
    private $menuAlias;

    /**
     * @var \Darvin\MenuBundle\Item\ItemFactoryInterface[]
     */
    private $itemFactories;

    /**
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface $customObjectLoader Custom object loader
     * @param \Knp\Menu\FactoryInterface                             $genericItemFactory Generic item factory
     * @param \Darvin\MenuBundle\Repository\Menu\ItemRepository      $menuItemRepository Menu item entity repository
     * @param string                                                 $menuAlias          Menu alias
     */
    public function __construct(
        CustomObjectLoaderInterface $customObjectLoader,
        FactoryInterface $genericItemFactory,
        ItemRepository $menuItemRepository,
        $menuAlias
    ) {
        $this->customObjectLoader = $customObjectLoader;
        $this->genericItemFactory = $genericItemFactory;
        $this->menuItemRepository = $menuItemRepository;
        $this->menuAlias = $menuAlias;

        $this->itemFactories = [];
    }

    /**
     * @param string                                       $associationClass Association class
     * @param \Darvin\MenuBundle\Item\ItemFactoryInterface $itemFactory      Item factory
     */
    public function addItemFactory($associationClass, ItemFactoryInterface $itemFactory)
    {
        $this->itemFactories[$associationClass] = $itemFactory;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu()
    {
        $root = $this->genericItemFactory->createItem($this->menuAlias);

        foreach ($this->getMenuItems() as $menuItem) {
            $associated = $menuItem->getAssociatedInstance();

            if (!empty($associated) && isset($this->itemFactories[$menuItem->getAssociatedClass()])) {
                $root->addChild($this->itemFactories[$menuItem->getAssociatedClass()]->createItem($associated));
            }
        }

        return $root;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    private function getMenuItems()
    {
        $menuItems = $this->menuItemRepository->getByMenuEnabledBuilder($this->menuAlias)->getQuery()->getResult();

        $this->customObjectLoader->loadCustomObjects($menuItems);

        return $menuItems;
    }
}
