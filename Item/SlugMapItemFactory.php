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

use Darvin\ContentBundle\Entity\SlugMapItem;
use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Item from slug map item entity factory
 */
class SlugMapItemFactory extends AbstractEntityItemFactory
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $uriRoute;

    /**
     * @param \Knp\Menu\FactoryInterface                 $genericItemFactory Generic item factory
     * @param \Doctrine\ORM\EntityManager                $em                 Entity manager
     * @param \Symfony\Component\Routing\RouterInterface $router             Router
     * @param string                                     $uriRoute           URI route
     */
    public function __construct(FactoryInterface $genericItemFactory, EntityManager $em, RouterInterface $router, $uriRoute)
    {
        parent::__construct($genericItemFactory, $em);

        $this->router = $router;
        $this->uriRoute = $uriRoute;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return string
     */
    public function getLabel($slugMapItem)
    {
        $this->validateEntity($slugMapItem);

        return (string) $slugMapItem->getObject();
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return string
     */
    public function getUri($slugMapItem)
    {
        $this->validateEntity($slugMapItem);

        return $this->router->generate($this->uriRoute, [
            'slug' => $slugMapItem->getSlug(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClass()
    {
        return SlugMapItem::class;
    }
}
