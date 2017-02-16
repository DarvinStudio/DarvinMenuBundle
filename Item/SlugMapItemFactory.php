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
 * Slug map item factory
 */
class SlugMapItemFactory extends AbstractItemFactory
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
     * @param \Doctrine\ORM\EntityManager                $em                 Entity manager
     * @param \Knp\Menu\FactoryInterface                 $genericItemFactory Generic item factory
     * @param \Symfony\Component\Routing\RouterInterface $router             Router
     * @param string                                     $uriRoute           URI route
     */
    public function __construct(EntityManager $em, FactoryInterface $genericItemFactory, RouterInterface $router, $uriRoute)
    {
        parent::__construct($em, $genericItemFactory);

        $this->router = $router;
        $this->uriRoute = $uriRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return SlugMapItem::class;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return string
     */
    protected function getLabel($slugMapItem)
    {
        return (string) $slugMapItem->getObject();
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return string
     */
    protected function getUri($slugMapItem)
    {
        return $this->router->generate($this->uriRoute, [
            'slug' => $slugMapItem->getSlug(),
        ]);
    }
}
