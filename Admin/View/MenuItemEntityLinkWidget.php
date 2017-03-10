<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\View;

use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\AbstractWidget;
use Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget;
use Darvin\MenuBundle\Entity\Menu\Item;

/**
 * Menu item entity link admin view widget
 */
class MenuItemEntityLinkWidget extends AbstractWidget
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @var \Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget
     */
    private $showLinkWidget;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter $adminRouter Admin router
     */
    public function setAdminRouter(AdminRouter $adminRouter)
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * @param \Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget $showLinkWidget Show link admin view widget
     */
    public function setShowLinkWidget(ShowLinkWidget $showLinkWidget)
    {
        $this->showLinkWidget = $showLinkWidget;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     * @param array                               $options  Options
     * @param string                              $property Property name
     *
     * @return string
     */
    protected function createContent($menuItem, array $options, $property)
    {
        if (null === $menuItem->getSlugMapItem()) {
            return null;
        }

        $entity = $menuItem->getSlugMapItem()->getObject();

        if (empty($entity)) {
            return null;
        }

        $content = $this->showLinkWidget->getContent($entity, [
            'text_link' => true,
        ]);

        return !empty($content) ? $content : (string) $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedEntityClasses()
    {
        return [
            Item::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return [
            Permission::VIEW,
        ];
    }
}
