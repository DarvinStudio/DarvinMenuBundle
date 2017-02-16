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

/**
 * Item from menu item entity factory
 */
class MenuItemFactory extends AbstractItemFactory
{
    /**
     * @var \Darvin\MenuBundle\Item\SlugMapItemFactory
     */
    protected $slugMapItemFactory;

    /**
     * @param \Doctrine\ORM\EntityManager                $em                 Entity manager
     * @param \Knp\Menu\FactoryInterface                 $genericItemFactory Generic item factory
     * @param \Darvin\MenuBundle\Item\SlugMapItemFactory $slugMapItemFactory Item from slug map item factory
     */
    public function __construct(EntityManager $em, FactoryInterface $genericItemFactory, SlugMapItemFactory $slugMapItemFactory)
    {
        parent::__construct($em, $genericItemFactory);

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

        return !empty($url) ? $url : $this->slugMapItemFactory->getUri($menuItem->getSlugMapItem());
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     *
     * @return array
     */
    protected function getExtras($menuItem)
    {
        return array_merge(parent::getExtras($menuItem), [
            'image'      => $menuItem->getImage(),
            'hoverImage' => $menuItem->getHoverImage(),
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
