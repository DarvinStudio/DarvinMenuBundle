<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Configuration;

/**
 * Menu
 */
class Menu
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var bool
     */
    private $breadcrumbsEnabled;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $builderId;

    /**
     * @var string
     */
    private $builderAlias;

    /**
     * @var string
     */
    private $breadcrumbsBuilderId;

    /**
     * @var string
     */
    private $breadcrumbsBuilderAlias;

    /**
     * @var string
     */
    private $menuServiceId;

    /**
     * @var string
     */
    private $menuServiceAlias;

    /**
     * @param string $alias              Alias
     * @param bool   $breadcrumbsEnabled Is breadcrumbs functionality enabled
     */
    public function __construct($alias, $breadcrumbsEnabled)
    {
        $this->alias = $alias;
        $this->breadcrumbsEnabled = $breadcrumbsEnabled;

        $this->title = 'menu.'.$alias;
        $this->builderId = 'darvin_menu.builder.'.$alias;
        $this->builderAlias = 'darvin_menu_'.$alias;
        $this->breadcrumbsBuilderId = 'darvin_menu.breadcrumbs_builder.'.$alias;
        $this->breadcrumbsBuilderAlias = 'darvin_breadcrumbs_'.$alias;
        $this->menuServiceId = 'darvin_menu.menu.'.$alias;
        $this->menuServiceAlias = 'darvin_menu_'.$alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return boolean
     */
    public function isBreadcrumbsEnabled()
    {
        return $this->breadcrumbsEnabled;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBuilderId()
    {
        return $this->builderId;
    }

    /**
     * @return string
     */
    public function getBuilderAlias()
    {
        return $this->builderAlias;
    }

    /**
     * @return string
     */
    public function getBreadcrumbsBuilderId()
    {
        return $this->breadcrumbsBuilderId;
    }

    /**
     * @return string
     */
    public function getBreadcrumbsBuilderAlias()
    {
        return $this->breadcrumbsBuilderAlias;
    }

    /**
     * @return string
     */
    public function getMenuServiceId()
    {
        return $this->menuServiceId;
    }

    /**
     * @return string
     */
    public function getMenuServiceAlias()
    {
        return $this->menuServiceAlias;
    }
}
