<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\Menu;

use Darvin\AdminBundle\Menu\ItemFactoryInterface;
use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\MenuBundle\Configuration\Menu;
use Darvin\MenuBundle\Configuration\MenuConfigurationInterface;
use Darvin\MenuBundle\Entity\Menu\Item;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Admin menu item factory
 */
class ItemFactory implements ItemFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfigurationInterface
     */
    private $menuConfig;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface                               $adminRouter          Admin router
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Darvin\MenuBundle\Configuration\MenuConfigurationInterface                  $menuConfig           Menu configuration
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface                   $metadataManager      Metadata manager
     */
    public function __construct(
        AdminRouterInterface $adminRouter,
        AuthorizationCheckerInterface $authorizationChecker,
        MenuConfigurationInterface $menuConfig,
        AdminMetadataManagerInterface $metadataManager
    ) {
        $this->adminRouter = $adminRouter;
        $this->authorizationChecker = $authorizationChecker;
        $this->menuConfig = $menuConfig;
        $this->metadataManager = $metadataManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(): iterable
    {
        if (!$this->authorizationChecker->isGranted(Permission::VIEW, Item::class)) {
            return;
        }

        $filterFormTypeName = $this->metadataManager->getMetadata(Item::class)->getFilterFormTypeName();

        foreach (array_values($this->menuConfig->getMenus()) as $i => $menu) {
            yield $this->createItem($menu, $filterFormTypeName, ($i + 1) * 100);
        }
    }

    /**
     * @param \Darvin\MenuBundle\Configuration\Menu $menu               Menu
     * @param string                                $filterFormTypeName Filter form type name
     * @param int                                   $position           Position
     *
     * @return \Darvin\AdminBundle\Menu\Item
     */
    private function createItem(Menu $menu, string $filterFormTypeName, int $position): \Darvin\AdminBundle\Menu\Item
    {
        $routeParams = [
            $filterFormTypeName => [
                'menu' => $menu->getName(),
            ],
        ];

        return (new \Darvin\AdminBundle\Menu\Item(sprintf('menu_%s', $menu->getName())))
            ->setAssociatedObject(Item::class)
            ->setIndexTitle($menu->getTitle())
            ->setIndexUrl($this->adminRouter->generate(null, Item::class, AdminRouterInterface::TYPE_INDEX, $routeParams))
            ->setParentName('menu')
            ->setPosition($position);
    }
}
