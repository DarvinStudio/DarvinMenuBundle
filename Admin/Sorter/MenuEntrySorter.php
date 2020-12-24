<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\Sorter;

use Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface;
use Darvin\Utils\Tree\TreeSorterInterface;

/**
 * Menu entry sorter
 */
class MenuEntrySorter implements MenuEntrySorterInterface
{
    /**
     * @var \Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface
     */
    private $contentReferenceObjectLoader;

    /**
     * @var \Darvin\Utils\Tree\TreeSorterInterface
     */
    private $treeSorter;

    /**
     * @param \Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface $contentReferenceObjectLoader Content reference object loader
     * @param \Darvin\Utils\Tree\TreeSorterInterface                                $treeSorter                   Tree sorter
     */
    public function __construct(ContentReferenceObjectLoaderInterface $contentReferenceObjectLoader, TreeSorterInterface $treeSorter)
    {
        $this->contentReferenceObjectLoader = $contentReferenceObjectLoader;
        $this->treeSorter = $treeSorter;
    }

    /**
     * {@inheritDoc}
     */
    public function sort(array $entries): array
    {
        if (empty($entries)) {
            return [];
        }

        $contentReferences = [];

        foreach ($entries as $entry) {
            if (null !== $entry->getContentReference()) {
                $contentReferences[] = $entry->getContentReference();
            }
        }

        $this->contentReferenceObjectLoader->loadObjects($contentReferences);

        return $this->treeSorter->sortTree($entries);
    }
}
