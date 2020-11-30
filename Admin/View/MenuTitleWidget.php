<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\View;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\AbstractWidget;
use Darvin\MenuBundle\Configuration\MenuConfigurationInterface;
use Darvin\MenuBundle\Entity\MenuItem;

/**
 * Menu title admin view widget
 */
class MenuTitleWidget extends AbstractWidget
{
    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfigurationInterface
     */
    private $menuConfig;

    /**
     * @param \Darvin\MenuBundle\Configuration\MenuConfigurationInterface $menuConfig Menu config
     */
    public function __construct(MenuConfigurationInterface $menuConfig)
    {
        $this->menuConfig = $menuConfig;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent(object $entity, array $options): ?string
    {
        /** @var \Darvin\MenuBundle\Entity\MenuItem $item */
        $item = $entity;

        if ($this->menuConfig->hasMenu($item->getMenu())) {
            return $this->menuConfig->getMenu($item->getMenu())->getTitle();
        }

        return $item->getMenu();
    }

    /**
     * {@inheritDoc}
     */
    protected function getAllowedEntityClasses(): iterable
    {
        yield MenuItem::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
