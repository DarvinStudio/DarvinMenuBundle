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
use Darvin\PageBundle\Config\PageConfig;
use Darvin\Utils\Routing\RouteManagerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Item from slug map item entity factory
 */
class SlugMapItemFactory extends AbstractEntityItemFactory
{
    /**
     * @var \Darvin\PageBundle\Config\PageConfig
     */
    protected $pageConfig;

    /**
     * @var \Darvin\Utils\Routing\RouteManagerInterface
     */
    protected $routeManager;

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
     * @param \Darvin\PageBundle\Config\PageConfig        $pageConfig       Page configuration
     * @param \Darvin\Utils\Routing\RouteManagerInterface $routeManager     Route manager
     * @param \Symfony\Component\Routing\RouterInterface  $router           Router
     * @param string                                      $genericUriRoute  Generic URI route
     * @param string                                      $homepageUriRoute Homepage URI route
     */
    public function __construct(
        PageConfig $pageConfig,
        RouteManagerInterface $routeManager,
        RouterInterface $router,
        string $genericUriRoute,
        string $homepageUriRoute
    ) {
        $this->pageConfig = $pageConfig;
        $this->routeManager = $routeManager;
        $this->router = $router;
        $this->genericUriRoute = $genericUriRoute;
        $this->homepageUriRoute = $homepageUriRoute;
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
        /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem */
        $slugMapItem = $source;

        $route  = $this->getUriRoute($slugMapItem);
        $params = [];

        if ($this->routeManager->hasRequirement($route, 'slug')) {
            $params['slug'] = $slugMapItem->getSlug();
        }

        return $this->router->generate($route, $params);
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

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClass(): string
    {
        return SlugMapItem::class;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return string
     */
    private function getUriRoute(SlugMapItem $slugMapItem): string
    {
        $homepage = $this->pageConfig->getHomepage();

        return !empty($homepage) && $homepage === $slugMapItem->getObject() ? $this->homepageUriRoute : $this->genericUriRoute;
    }
}
