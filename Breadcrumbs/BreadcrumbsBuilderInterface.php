<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Breadcrumbs;

use Knp\Menu\ItemInterface;

/**
 * Breadcrumbs builder
 */
interface BreadcrumbsBuilderInterface
{
    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildBreadcrumbs(): ItemInterface;
}
