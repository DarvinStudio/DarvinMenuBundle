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
use Darvin\MenuBundle\Entity\MenuEntry;
use Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface;

/**
 * Menu title admin view widget
 */
class MenuTitleWidget extends AbstractWidget
{
    /**
     * @var \Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface
     */
    private $menuProvider;

    /**
     * @param \Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface $menuProvider Menu provider
     */
    public function __construct(MenuProviderRegistryInterface $menuProvider)
    {
        $this->menuProvider = $menuProvider;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent(object $entity, array $options): ?string
    {
        /** @var \Darvin\MenuBundle\Entity\MenuEntry $entry */
        $entry = $entity;

        if ($this->menuProvider->exists($entry->getMenu())) {
            return $this->menuProvider->getMenu($entry->getMenu())->getTitle();
        }

        return $entry->getMenu();
    }

    /**
     * {@inheritDoc}
     */
    protected function getAllowedEntityClasses(): iterable
    {
        yield MenuEntry::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
