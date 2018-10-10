<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item;

use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Item from menu item entity factory
 */
class MenuItemFactory extends AbstractEntityItemFactory
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var \Darvin\MenuBundle\Item\SlugMapItemFactory
     */
    protected $slugMapItemFactory;

    /**
     * @param \Knp\Menu\FactoryInterface                     $genericItemFactory Generic item factory
     * @param \Doctrine\ORM\EntityManager                    $em                 Entity manager
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer        Object namer
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack       Request stack
     * @param \Darvin\MenuBundle\Item\SlugMapItemFactory     $slugMapItemFactory Item from slug map item factory
     */
    public function __construct(
        FactoryInterface $genericItemFactory,
        EntityManager $em,
        ObjectNamerInterface $objectNamer,
        RequestStack $requestStack,
        SlugMapItemFactory $slugMapItemFactory
    ) {
        parent::__construct($genericItemFactory, $em, $objectNamer);

        $this->requestStack = $requestStack;
        $this->slugMapItemFactory = $slugMapItemFactory;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     *
     * @return string
     */
    protected function getLabel($menuItem)
    {
        $title = $menuItem->getTitle();

        return !empty($title) ? $title : $this->slugMapItemFactory->getLabel($menuItem->getSlugMapItem());
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     *
     * @return string
     */
    protected function getUri($menuItem)
    {
        $url = $menuItem->getUrl();

        if (empty($url)) {
            return null !== $menuItem->getSlugMapItem() ? $this->slugMapItemFactory->getUri($menuItem->getSlugMapItem()) : null;
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
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     *
     * @return array
     */
    protected function getExtras($menuItem)
    {
        $objectName = $objectId = $object = null;
        $image = $menuItem->getImage();
        $hoverImage = $menuItem->getHoverImage();

        if (null !== $menuItem->getSlugMapItem() && null !== $menuItem->getSlugMapItem()->getObject()) {
            $slugMapItem = $menuItem->getSlugMapItem();

            $objectName = $this->objectNamer->name($slugMapItem->getObjectClass());
            $objectId = $slugMapItem->getObjectId();

            $object = $slugMapItem->getObject();

            if (empty($image) && interface_exists('Darvin\ImageBundle\ImageableEntity\ImageableEntityInterface')) {
                if ($object instanceof \Darvin\ImageBundle\ImageableEntity\ImageableEntityInterface) {
                    $image = $object->getImage();
                    $hoverImage = !empty($hoverImage) ? $hoverImage : $object->getImage();
                }
            }
        }

        return array_merge(parent::getExtras($menuItem), [
            'objectName'          => $objectName,
            'objectId'            => $objectId,
            'object'              => $object,
            'image'               => $image,
            'hoverImage'          => $hoverImage,
            'showSlugMapChildren' => $menuItem->isShowChildren(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClass()
    {
        return Item::class;
    }
}
