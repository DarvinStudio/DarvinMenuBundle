<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item;

/**
 * Item factory
 */
interface ItemFactoryInterface
{
    /**
     * @param object $associated  Associated
     * @param bool   $addChildren Whether to add child items
     * @param string $locale      Locale
     * @param int    $depth       Menu depth
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createItem($associated, $addChildren, $locale, $depth = null);
}
