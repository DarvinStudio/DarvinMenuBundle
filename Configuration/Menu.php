<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
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
    private $icon;

    /**
     * @var array
     */
    private $builderOptions = [];

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
     * @param string $icon               Icon
     * @param array  $builderOptions     Builder options
     */
    public function __construct(string $alias, bool $breadcrumbsEnabled, string $icon, array $builderOptions = [])
    {
        $this->alias              = $alias;
        $this->breadcrumbsEnabled = $breadcrumbsEnabled;
        $this->icon               = $icon;
        $this->builderOptions     = $builderOptions;

        $this->title                   = sprintf('menu.%s', $alias);
        $this->builderId               = sprintf('darvin_menu.builder.%s', $alias);
        $this->builderAlias            = sprintf('darvin_menu_%s', $alias);
        $this->breadcrumbsBuilderId    = sprintf('darvin_menu.breadcrumbs_builder.%s', $alias);
        $this->breadcrumbsBuilderAlias = sprintf('darvin_breadcrumbs_%s', $alias);
        $this->menuServiceId           = sprintf('darvin_menu.menu.%s', $alias);
        $this->menuServiceAlias        = sprintf('darvin_menu_%s', $alias);
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return boolean
     */
    public function isBreadcrumbsEnabled(): bool
    {
        return $this->breadcrumbsEnabled;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return array
     */
    public function getBuilderOptions(): array
    {
        return $this->builderOptions;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBuilderId(): string
    {
        return $this->builderId;
    }

    /**
     * @return string
     */
    public function getBuilderAlias(): string
    {
        return $this->builderAlias;
    }

    /**
     * @return string
     */
    public function getBreadcrumbsBuilderId(): string
    {
        return $this->breadcrumbsBuilderId;
    }

    /**
     * @return string
     */
    public function getBreadcrumbsBuilderAlias(): string
    {
        return $this->breadcrumbsBuilderAlias;
    }

    /**
     * @return string
     */
    public function getMenuServiceId(): string
    {
        return $this->menuServiceId;
    }

    /**
     * @return string
     */
    public function getMenuServiceAlias(): string
    {
        return $this->menuServiceAlias;
    }
}
