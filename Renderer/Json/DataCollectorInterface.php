<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Renderer\Json;

use Knp\Menu\ItemInterface;

/**
 * JSON renderer data collector
 */
interface DataCollectorInterface
{
    /**
     * @param \Knp\Menu\ItemInterface $item Menu item
     * @param array                   $ids  IDs
     *
     * @return array
     */
    public function getData(ItemInterface $item, array $ids): array;
}
