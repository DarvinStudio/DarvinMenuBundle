<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Knp\Item\Factory;

use Knp\Menu\ItemInterface;

/**
 * KNP menu item factory
 */
interface KnpItemFactoryInterface
{
    /**
     * @param mixed $source Source
     *
     * @return \Knp\Menu\ItemInterface
     * @throws \InvalidArgumentException
     */
    public function createItem($source): ItemInterface;

    /**
     * @param mixed $source Source
     *
     * @return bool
     */
    public function supports($source): bool;
}
