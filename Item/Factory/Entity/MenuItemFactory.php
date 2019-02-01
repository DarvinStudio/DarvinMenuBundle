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
use Darvin\MenuBundle\Entity\Menu\Item;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Item from menu item entity factory
 */
class MenuItemFactory extends AbstractEntityItemFactory
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
        return $source instanceof Item;
    }

    /**
     * {@inheritDoc}
     */
    protected function getLabel($source): ?string
    {
        /** @var \Darvin\MenuBundle\Entity\Menu\Item $menuItem */
        $menuItem = $source;

        $title = $menuItem->getTitle();

        if (!empty($title)) {
            return $title;
        }
        if (null === $menuItem->getSlugMapItem()) {
            return $menuItem->getUrl();
        }

        return (string)$menuItem->getSlugMapItem()->getObject();
    }

    /**
     * {@inheritDoc}
     */
    protected function getUri($source): ?string
    {
        /** @var \Darvin\MenuBundle\Entity\Menu\Item $menuItem */
        $menuItem = $source;

        $url = $menuItem->getUrl();

        if (empty($url)) {
            return $this->slugMapRouter->generateUrl($menuItem->getSlugMapItem());
        }
        if (0 !== strpos($url, '/') || 0 === strpos($url, '//')) {
            return $url;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return $url;
        }

        $baseUrl = $request->getBaseUrl();

        if (!empty($baseUrl) && 0 !== strpos($url, $baseUrl)) {
            $url = $baseUrl.$url;
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtras($source): array
    {
        /** @var \Darvin\MenuBundle\Entity\Menu\Item $menuItem */
        $menuItem = $source;

        $image      = $menuItem->getImage();
        $hoverImage = $menuItem->getHoverImage();
        $object = $objectId = $objectName = null;

        if (null !== $menuItem->getSlugMapItem() && null !== $menuItem->getSlugMapItem()->getObject()) {
            $slugMapItem = $menuItem->getSlugMapItem();

            $object     = $slugMapItem->getObject();
            $objectId   = $slugMapItem->getObjectId();
            $objectName = $this->objectNamer->name($slugMapItem->getObjectClass());

            if (empty($image) && $object instanceof ImageableInterface) {
                $image      = $object->getImage();
                $hoverImage = !empty($hoverImage) ? $hoverImage : $object->getImage();
            }
        }

        return array_merge(parent::getExtras($menuItem), [
            'hoverImage' => $hoverImage,
            'image'      => $image,
            'itemEntity' => $menuItem,
            'object'     => $object,
            'objectId'   => $objectId,
            'objectName' => $objectName,
        ]);
    }
}
