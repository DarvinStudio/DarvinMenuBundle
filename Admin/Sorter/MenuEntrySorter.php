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

use Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface;
use Darvin\Utils\Tree\TreeSorterInterface;

/**
 * Menu entry sorter
 */
class MenuEntrySorter implements MenuEntrySorterInterface
{
    /**
     * @var \Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface
     */
    private $slugMapObjectLoader;

    /**
     * @var \Darvin\Utils\Tree\TreeSorterInterface
     */
    private $treeSorter;

    /**
     * @param \Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface $slugMapObjectLoader Slug map object loader
     * @param \Darvin\Utils\Tree\TreeSorterInterface                  $treeSorter          Tree sorter
     */
    public function __construct(SlugMapObjectLoaderInterface $slugMapObjectLoader, TreeSorterInterface $treeSorter)
    {
        $this->slugMapObjectLoader = $slugMapObjectLoader;
        $this->treeSorter = $treeSorter;
    }

    /**
     * {@inheritDoc}
     */
    public function sort(array $entries): array
    {
        if (empty($entries)) {
            return $entries;
        }

        $slugMapItems = [];

        foreach ($entries as $entry) {
            if (null !== $entry->getSlugMapItem()) {
                $slugMapItems[] = $entry->getSlugMapItem();
            }
        }

        $this->slugMapObjectLoader->loadObjects($slugMapItems);

        return $this->treeSorter->sortTree($entries);
    }
}
