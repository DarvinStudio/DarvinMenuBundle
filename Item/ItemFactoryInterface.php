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
     * @param object $entity Entity
     *
     * @return \Knp\Menu\ItemInterface
     * @throws \Darvin\MenuBundle\Item\ItemFactoryException
     */
    public function createItem($entity);

    /**
     * @param string $class Entity class
     *
     * @return bool
     */
    public function supportsClass($class);
}
