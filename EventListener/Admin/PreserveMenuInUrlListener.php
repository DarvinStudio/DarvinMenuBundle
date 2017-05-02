<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\EventListener\Admin;

use Darvin\AdminBundle\Event\Router\RouteEvent;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\MenuBundle\Configuration\MenuConfiguration;
use Darvin\MenuBundle\Entity\Menu\Item;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Preserve menu in URL admin event listener
 */
class PreserveMenuInUrlListener
{
    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private $menuConfig;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration $menuConfig      Menu configuration
     * @param \Darvin\AdminBundle\Metadata\MetadataManager       $metadataManager Admin metadata manager
     * @param \Symfony\Component\HttpFoundation\RequestStack     $requestStack    Request stack
     */
    public function __construct(MenuConfiguration $menuConfig, MetadataManager $metadataManager, RequestStack $requestStack)
    {
        $this->menuConfig = $menuConfig;
        $this->metadataManager = $metadataManager;
        $this->requestStack = $requestStack;
    }

    /**
     * @param \Darvin\AdminBundle\Event\Router\RouteEvent $event Event
     */
    public function preRouteGenerate(RouteEvent $event)
    {
        if (Item::class !== $event->getEntityClass() && !in_array(Item::class, class_parents($event->getEntityClass()))) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return;
        }

        $params = $event->getParams();
        $param = $this->metadataManager->getMetadata($event->getEntityClass())->getFilterFormTypeName();

        if (isset($params[$param]['menu'])) {
            return;
        }
        if (!$request->query->has($param)) {
            return;
        }

        $filterData = $request->query->get($param);

        if (!isset($filterData['menu'])) {
            return;
        }

        $alias = $filterData['menu'];

        $menus = $this->menuConfig->getMenus();

        if (!isset($menus[$alias])) {
            return;
        }
        if (!isset($params[$param])) {
            $params[$param] = [];
        }
        if (!is_array($params[$param])) {
            return;
        }

        $params[$param]['menu'] = $alias;

        $event->setParams($params);
    }
}
