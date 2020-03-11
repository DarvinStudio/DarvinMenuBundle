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
interface JsonRendererInterface
{
    /**
     * @param \Knp\Menu\ItemInterface $item Menu item
     *
     * @return string
     */
    public function renderJson(ItemInterface $item): string;
}
