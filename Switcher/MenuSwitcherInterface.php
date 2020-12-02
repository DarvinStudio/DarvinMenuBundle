<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Switcher;

/**
 * Menu switcher
 */
interface MenuSwitcherInterface
{
    /**
     * @param object $entity Entity
     *
     * @return string[]
     */
    public function getDefaultMenus(object $entity): array;

    /**
     * @return array
     */
    public function getMenusToEnable(): array;

    /**
     * @return array
     */
    public function getMenusToDisable(): array;

    /**
     * @param object $entity Entity
     *
     * @return bool
     */
    public function hasEnabledMenus(object $entity): bool;

    /**
     * @param object $entity   Entity
     * @param string $menuName Menu name
     *
     * @return bool
     */
    public function isMenuEnabled(object $entity, string $menuName): bool;

    /**
     * @param object $entity   Entity
     * @param string $menuName Menu name
     * @param bool   $enable   Whether to enable menu
     */
    public function toggleMenu(object $entity, string $menuName, bool $enable): void;
}
