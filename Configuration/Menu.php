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
     * @param string $alias Alias
     */
    public function __construct(string $alias)
    {
        $this->alias = $alias;

        $this->title        = sprintf('menu.%s', $alias);
        $this->builderId    = sprintf('darvin_menu.builder.%s', $alias);
        $this->builderAlias = sprintf('darvin_menu_%s', $alias);
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
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
}
