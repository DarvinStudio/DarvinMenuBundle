<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item\Factory\Entity;

use Darvin\ImageBundle\Imageable\ImageableInterface;
use Darvin\MenuBundle\Entity\MenuEntry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Menu entry KNP menu item factory
 */
class MenuEntryFactory extends AbstractEntityItemFactory
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($source): bool
    {
        return $source instanceof MenuEntry;
    }

    /**
     * {@inheritDoc}
     */
    protected function getLabel($source): ?string
    {
        /** @var \Darvin\MenuBundle\Entity\MenuEntry $entry */
        $entry = $source;

        $title = $entry->getTitle();

        if (null !== $title) {
            return $title;
        }
        if (null === $entry->getSlugMapItem()) {
            return $entry->getUrl();
        }

        return (string)$entry->getSlugMapItem()->getObject();
    }

    /**
     * {@inheritDoc}
     */
    protected function getUri($source): ?string
    {
        /** @var \Darvin\MenuBundle\Entity\MenuEntry $entry */
        $entry = $source;

        $url = $entry->getUrl();

        if (null === $url) {
            return $this->slugMapRouter->generateUrl($entry->getSlugMapItem());
        }
        if (0 !== strpos($url, '/') || 0 === strpos($url, '//')) {
            return $url;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return $url;
        }

        $baseUrl = $request->getBaseUrl();

        if ('' !== $baseUrl && 0 !== strpos($url, $baseUrl)) {
            $url = $baseUrl.$url;
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtras($source): array
    {
        /** @var \Darvin\MenuBundle\Entity\MenuEntry $entry */
        $entry = $source;

        $image      = $entry->getImage();
        $hoverImage = $entry->getHoverImage();
        $object = $objectId = $objectName = null;

        if (null !== $entry->getSlugMapItem() && null !== $entry->getSlugMapItem()->getObject()) {
            $slugMapItem = $entry->getSlugMapItem();

            $object     = $slugMapItem->getObject();
            $objectId   = $slugMapItem->getObjectId();
            $objectName = $this->objectNamer->name($slugMapItem->getObjectClass());

            if (null === $image && $object instanceof ImageableInterface) {
                $image      = $object->getImage();
                $hoverImage = null !== $hoverImage ? $hoverImage : $object->getImage();
            }
        }

        return array_merge(parent::getExtras($entry), [
            'entry'      => $entry,
            'hoverImage' => $hoverImage,
            'image'      => $image,
            'object'     => $object,
            'objectId'   => $objectId,
            'objectName' => $objectName,
        ]);
    }
}
