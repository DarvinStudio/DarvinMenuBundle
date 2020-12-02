<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\Menu;

use Darvin\AdminBundle\Menu\Item;
use Darvin\AdminBundle\Menu\ItemFactoryInterface;
use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\MenuBundle\Entity\MenuEntry;
use Darvin\MenuBundle\Provider\Model\Menu;
use Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Admin menu item factory
 */
class MenuItemFactory implements ItemFactoryInterface
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
     * @var \Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface
     */
    private $menuProvider;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface                               $adminRouter          Admin router
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface           $menuProvider         Menu provider
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface                   $metadataManager      Metadata manager
     */
    public function __construct(
        AdminRouterInterface $adminRouter,
        AuthorizationCheckerInterface $authorizationChecker,
        MenuProviderRegistryInterface $menuProvider,
        AdminMetadataManagerInterface $metadataManager
    ) {
        $this->adminRouter = $adminRouter;
        $this->authorizationChecker = $authorizationChecker;
        $this->menuProvider = $menuProvider;
        $this->metadataManager = $metadataManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(): iterable
    {
        if (!$this->authorizationChecker->isGranted(Permission::VIEW, MenuEntry::class)) {
            return;
        }

        $filterFormTypeName = $this->metadataManager->getMetadata(MenuEntry::class)->getFilterFormTypeName();

        foreach (array_values($this->menuProvider->getMenuCollection()) as $i => $menu) {
            yield $this->createItem($menu, $filterFormTypeName, ($i + 1) * 100);
        }
    }

    /**
     * @param \Darvin\MenuBundle\Provider\Model\Menu $menu               Menu
     * @param string                                 $filterFormTypeName Filter form type name
     * @param int                                    $position           Position
     *
     * @return \Darvin\AdminBundle\Menu\Item
     */
    private function createItem(Menu $menu, string $filterFormTypeName, int $position): Item
    {
        $routeParams = [
            $filterFormTypeName => [
                'menu' => $menu->getName(),
            ],
        ];

        return (new Item(sprintf('menu_%s', $menu->getName())))
            ->setAssociatedObject(MenuEntry::class)
            ->setIndexTitle($menu->getTitle())
            ->setIndexUrl($this->adminRouter->generate(null, MenuEntry::class, AdminRouterInterface::TYPE_INDEX, $routeParams))
            ->setParentName('menu')
            ->setPosition($position);
    }
}
