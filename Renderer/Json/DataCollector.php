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
class DataCollector implements DataCollectorInterface
{
    /**
     * {@inheritDoc}
     */
    public function getData(ItemInterface $item, array $ids): array
    {
        return [
            'id'       => $ids[$item->getName()],
            'name'     => $item->getLabel(),
            'href'     => $item->getUri(),
            'hasChild' => $item->hasChildren(),
            'parentId' => null !== $item->getParent() && !$item->getParent()->isRoot() ? $ids[$item->getParent()->getName()] : null,
        ];
    }
}
