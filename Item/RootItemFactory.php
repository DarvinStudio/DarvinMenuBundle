<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item;

/**
 * Root item factory
 */
class RootItemFactory extends AbstractItemFactory
{
    /**
     * @param string $menuAlias Menu alias
     *
     * @return string
     */
    protected function getItemName($menuAlias)
    {
        return $menuAlias;
    }
}
