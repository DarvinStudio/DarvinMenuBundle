<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Slug;

/**
 * Slug map object loader
 */
interface SlugMapObjectLoaderInterface
{
    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem[] $items Slug map items
     */
    public function loadObjects(array $items): void;
}
