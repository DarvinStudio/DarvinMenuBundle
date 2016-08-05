<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\View;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\AbstractWidget;
use Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget;
use Darvin\MenuBundle\Configuration\AssociationConfiguration;
use Darvin\MenuBundle\Entity\Menu\Item;

/**
 * Associated admin view widget
 */
class AssociatedWidget extends AbstractWidget
{
    /**
     * @var \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private $associationConfig;

    /**
     * @var \Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget
     */
    private $showLinkWidget;

    /**
     * @param \Darvin\MenuBundle\Configuration\AssociationConfiguration $associationConfig Association configuration
     */
    public function setAssociationConfig(AssociationConfiguration $associationConfig)
    {
        $this->associationConfig = $associationConfig;
    }

    /**
     * @param \Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget $showLinkWidget Show link view widget
     */
    public function setShowLinkWidget(ShowLinkWidget $showLinkWidget)
    {
        $this->showLinkWidget = $showLinkWidget;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'menu_associated';
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
        $associatedClass = $menuItem->getAssociatedClass();

        if (empty($associatedClass) || !$this->associationConfig->hasAssociationClass($associatedClass)) {
            return null;
        }

        $title = $this->associationConfig->getAssociationByClass($associatedClass)->getTitle();

        $associated = $menuItem->getAssociatedInstance();

        if (empty($associated)) {
            return $title;
        }

        $showLink = $this->showLinkWidget->getContent($associated);

        return !empty($showLink)
            ? $this->render([], [
                'show_link' => $showLink,
                'title'     => $title,
            ])
            : $title;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedEntityClasses()
    {
        return [
            Item::ITEM_CLASS,
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

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTemplate()
    {
        return 'DarvinMenuBundle:Admin/widget:associated.html.twig';
    }
}
