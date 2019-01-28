<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\Sorter;

use Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader;
use Darvin\Utils\Tree\TreeSorterInterface;

/**
 * Menu item sorter
 */
class MenuItemSorter
{
    /**
     * @var \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader
     */
    private $slugMapItemCustomObjectLoader;

    /**
     * @var \Darvin\Utils\Tree\TreeSorterInterface
     */
    private $treeSorter;

    /**
     * @param \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader $slugMapItemCustomObjectLoader Slug map item custom object loader
     * @param \Darvin\Utils\Tree\TreeSorterInterface                   $treeSorter                    Tree sorter
     */
    public function __construct(SlugMapItemCustomObjectLoader $slugMapItemCustomObjectLoader, TreeSorterInterface $treeSorter)
    {
        $this->slugMapItemCustomObjectLoader = $slugMapItemCustomObjectLoader;
        $this->treeSorter = $treeSorter;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item[] $menuItems Menu items
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     * @throws \Darvin\Utils\Tree\Exception\ClassIsNotTreeException
     */
    public function sort(array $menuItems): array
    {
        if (empty($menuItems)) {
            return $menuItems;
        }

        $slugMapItems = [];

        foreach ($menuItems as $menuItem) {
            if (null !== $menuItem->getSlugMapItem()) {
                $slugMapItems[] = $menuItem->getSlugMapItem();
            }
        }

        $this->slugMapItemCustomObjectLoader->loadCustomObjects($slugMapItems);

        return $this->treeSorter->sortTree($menuItems);
    }
}
