<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Provider\Registry;

use Darvin\MenuBundle\Provider\Model\Menu;

/**
 * Menu provider registry
 */
interface MenuProviderRegistryInterface
{
    /**
     * @param string $name Menu name
     *
     * @return \Darvin\MenuBundle\Provider\Model\Menu
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getMenu(string $name): Menu;

    /**
     * @param string $name Menu name
     *
     * @return bool
     * @throws \LogicException
     */
    public function exists(string $name): bool;

    /**
     * @return \Darvin\MenuBundle\Provider\Model\Menu[]
     * @throws \LogicException
     */
    public function getMenuCollection(): array;
}
