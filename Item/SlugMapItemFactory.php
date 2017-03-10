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
use Darvin\PageBundle\Configuration\Configuration;
use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Item from slug map item entity factory
 */
class SlugMapItemFactory extends AbstractEntityItemFactory
{
    /**
     * @var \Darvin\PageBundle\Configuration\Configuration
     */
    protected $pageConfig;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $genericUriRoute;

    /**
     * @var string
     */
    protected $homepageUriRoute;

    /**
     * @param \Knp\Menu\FactoryInterface                     $genericItemFactory Generic item factory
     * @param \Doctrine\ORM\EntityManager                    $em                 Entity manager
     * @param \Darvin\PageBundle\Configuration\Configuration $pageConfig         Page configuration
     * @param \Symfony\Component\Routing\RouterInterface     $router             Router
     * @param string                                         $genericUriRoute    Generic URI route
     * @param string                                         $homepageUriRoute   Homepage URI route
     */
    public function __construct(
        FactoryInterface $genericItemFactory,
        EntityManager $em,
        Configuration $pageConfig,
        RouterInterface $router,
        $genericUriRoute,
        $homepageUriRoute
    ) {
        parent::__construct($genericItemFactory, $em);

        $this->pageConfig = $pageConfig;
        $this->router = $router;
        $this->genericUriRoute = $genericUriRoute;
        $this->homepageUriRoute = $homepageUriRoute;
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

        return $this->router->generate($this->getUriRoute($slugMapItem), [
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

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return string
     */
    private function getUriRoute(SlugMapItem $slugMapItem)
    {
        $homepage = $this->pageConfig->getHomepage();

        return !empty($homepage) && $homepage === $slugMapItem->getObject() ? $this->homepageUriRoute : $this->genericUriRoute;
    }
}
