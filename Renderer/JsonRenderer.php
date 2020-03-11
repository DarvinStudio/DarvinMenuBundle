<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Renderer;

use Knp\Menu\ItemInterface;

/**
 * JSON renderer
 */
class JsonRenderer implements JsonRendererInterface
{
    /**
     * {@inheritDoc}
     */
    public function renderJson(ItemInterface $item): string
    {
        return json_encode($this->buildArray($item));
    }

    /**
     * @param \Knp\Menu\ItemInterface $item Menu item
     *
     * @return array
     */
    protected function buildArray(ItemInterface $item): array
    {
        $array = [];

        foreach ($item->getChildren() as $child) {
            if (!$child->isDisplayed()) {
                continue;
            }

            $array[] = array_filter($this->toArray($child), function ($value): bool {
                return null !== $value;
            });

            if ($child->hasChildren()) {
                $array = array_merge($array, $this->buildArray($child));
            }
        }

        return $array;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item Menu item
     *
     * @return array
     */
    protected function toArray(ItemInterface $item): array
    {
        return [
            'id'       => $item->getName(),
            'name'     => $item->getLabel(),
            'href'     => $item->getUri(),
            'hasChild' => $item->hasChildren(),
            'parentId' => null !== $item->getParent() && $item->getParent()->getLevel() > 0 ? $item->getParent()->getName() : null,
        ];
    }
}
