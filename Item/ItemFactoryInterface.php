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
     * @param string $locale      Locale
     * @param bool   $addChildren Whether to add child items
     * @param array  $options     Options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createItem($associated, $locale, $addChildren = false, array $options = []);

    /**
     * @param object $associated Associated
     * @param array  $options    Options
     *
     * @return bool
     */
    public function canCreateItem($associated, array $options = []);
}
