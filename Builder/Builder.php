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

use Darvin\MenuBundle\Repository\Menu\ItemRepository;
use Knp\Menu\FactoryInterface;

/**
 * Builder
 */
class Builder
{
    const BUILD_METHOD = 'buildMenu';

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $itemFactory;

    /**
     * @var \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private $menuItemRepository;

    /**
     * @var string
     */
    private $menuAlias;

    /**
     * @param \Knp\Menu\FactoryInterface                        $itemFactory        Item factory
     * @param \Darvin\MenuBundle\Repository\Menu\ItemRepository $menuItemRepository Menu item entity repository
     * @param string                                            $menuAlias          Menu alias
     */
    public function __construct(FactoryInterface $itemFactory, ItemRepository $menuItemRepository, $menuAlias)
    {
        $this->itemFactory = $itemFactory;
        $this->menuItemRepository = $menuItemRepository;
        $this->menuAlias = $menuAlias;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu()
    {
        $root = $this->itemFactory->createItem($this->menuAlias);

        foreach ($this->getMenuItems() as $menuItem) {
            $title = $menuItem->getTitle();

            if (!empty($title)) {
                $root->addChild($this->itemFactory->createItem($title));
            }
        }

        return $root;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    private function getMenuItems()
    {
        return $this->menuItemRepository->getByMenuEnabledBuilder($this->menuAlias)->getQuery()->getResult();
    }
}
