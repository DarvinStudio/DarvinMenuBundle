<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Builder;

use Darvin\MenuBundle\Configuration\AssociationConfiguration;
use Darvin\MenuBundle\Repository\Menu\ItemRepository;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Knp\Menu\FactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Builder
 */
class Builder
{
    const BUILD_METHOD = 'buildMenu';

    /**
     * @var \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private $associationConfig;

    /**
     * @var \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    private $customObjectLoader;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $itemFactory;

    /**
     * @var \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private $menuItemRepository;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var string
     */
    private $menuAlias;

    /**
     * @param \Darvin\MenuBundle\Configuration\AssociationConfiguration   $associationConfig  Association configuration
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface      $customObjectLoader Custom object loader
     * @param \Knp\Menu\FactoryInterface                                  $itemFactory        Item factory
     * @param \Darvin\MenuBundle\Repository\Menu\ItemRepository           $menuItemRepository Menu item entity repository
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor   Property accessor
     * @param string                                                      $menuAlias          Menu alias
     */
    public function __construct(
        AssociationConfiguration $associationConfig,
        CustomObjectLoaderInterface $customObjectLoader,
        FactoryInterface $itemFactory,
        ItemRepository $menuItemRepository,
        PropertyAccessorInterface $propertyAccessor,
        $menuAlias
    ) {
        $this->associationConfig = $associationConfig;
        $this->customObjectLoader = $customObjectLoader;
        $this->itemFactory = $itemFactory;
        $this->menuItemRepository = $menuItemRepository;
        $this->propertyAccessor = $propertyAccessor;
        $this->menuAlias = $menuAlias;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu()
    {
        $root = $this->itemFactory->createItem($this->menuAlias);

        foreach ($this->getMenuItems() as $menuItem) {
            $title = $menuItem->getTitle();

            if (empty($title)) {
                continue;
            }

            $options = [];

            $associated = $menuItem->getAssociatedInstance();

            if (!empty($associated)) {
                $association = $this->associationConfig->getAssociationByClass($menuItem->getAssociatedClass());
                $options['route'] = $association->getRouteName();
                $options['routeParameters'] = [];

                foreach ($association->getRouteParams() as $param => $property) {
                    $options['routeParameters'][$param] = $this->propertyAccessor->getValue($associated, $property);
                }
            }

            $root->addChild($this->itemFactory->createItem($title, $options));
        }

        return $root;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    private function getMenuItems()
    {
        $menuItems = $this->menuItemRepository->getByMenuEnabledBuilder($this->menuAlias)->getQuery()->getResult();

        $this->customObjectLoader->loadCustomObjects($menuItems);

        return $menuItems;
    }
}
