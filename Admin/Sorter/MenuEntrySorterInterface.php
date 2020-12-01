<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\Sorter;

/**
 * Menu entry sorter
 */
interface MenuEntrySorterInterface
{
    /**
     * @param \Darvin\MenuBundle\Entity\MenuEntry[] $entries Menu entries
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntry[]
     * @throws \Darvin\Utils\Tree\Exception\ClassIsNotTreeException
     */
    public function sort(array $entries): array;
}
