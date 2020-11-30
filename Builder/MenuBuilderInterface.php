<?php declare(strict_types=1);
/**
 * @author    Lev Semin <lev@darvin-studio.ru>
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Builder;

use Knp\Menu\ItemInterface;

/**
 * Menu builder
 */
interface MenuBuilderInterface
{
    /**
     * @param array $options Options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu(array $options = []): ItemInterface;
}
