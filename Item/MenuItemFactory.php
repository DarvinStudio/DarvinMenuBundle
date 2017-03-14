<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item;

use Darvin\MenuBundle\Entity\Menu\Item;
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
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack       Request stack
     * @param \Darvin\MenuBundle\Item\SlugMapItemFactory     $slugMapItemFactory Item from slug map item factory
     */
    public function __construct(
        FactoryInterface $genericItemFactory,
        EntityManager $em,
        RequestStack $requestStack,
        SlugMapItemFactory $slugMapItemFactory
    ) {
        parent::__construct($genericItemFactory, $em);

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
            return $this->slugMapItemFactory->getUri($menuItem->getSlugMapItem());
        }
        if (0 !== strpos($url, '/') || 0 === strpos($url, '//')) {
            return $url;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return $url;
        }

        $baseUrl = $request->getBaseUrl();

        if (0 !== strpos($url, $baseUrl)) {
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
        return array_merge(parent::getExtras($menuItem), [
            'image'               => $menuItem->getImage(),
            'hoverImage'          => $menuItem->getHoverImage(),
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
