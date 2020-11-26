<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Configuration;

/**
 * Menu configuration
 */
interface MenuConfigurationInterface
{
    /**
     * @param string $alias Menu alias
     *
     * @return \Darvin\MenuBundle\Configuration\Menu
     * @throws \InvalidArgumentException
     */
    public function getMenu(string $alias): Menu;

    /**
     * @param string $alias Menu alias
     *
     * @return bool
     */
    public function hasMenu(string $alias): bool;

    /**
     * @return \Darvin\MenuBundle\Configuration\Menu[]
     *
     * @throws \LogicException
     */
    public function getMenus(): array;
}
