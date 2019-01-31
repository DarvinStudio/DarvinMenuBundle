<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item;

use Darvin\ContentBundle\Entity\SlugMapItem;

/**
 * Item from slug map item entity factory
 */
class SlugMapItemFactory extends AbstractEntityItemFactory
{
    /**
     * {@inheritDoc}
     */
    public function supports($source): bool
    {
        return $source instanceof SlugMapItem;
    }

    /**
     * {@inheritDoc}
     */
    protected function getLabel($source): ?string
    {
        /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem */
        $slugMapItem = $source;

        return (string)$slugMapItem->getObject();
    }

    /**
     * {@inheritDoc}
     */
    protected function getUri($source): ?string
    {
        return $this->slugMapRouter->generateUrl($source);
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtras($source): array
    {
        /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem */
        $slugMapItem = $source;

        return [
            'object'     => $slugMapItem->getObject(),
            'objectId'   => $slugMapItem->getObjectId(),
            'objectName' => $this->objectNamer->name($slugMapItem->getObjectClass()),
        ];
    }
}
