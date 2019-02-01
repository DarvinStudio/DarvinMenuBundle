<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: levsemin
 * Date: 25.04.17
 * Time: 23:06
 */

namespace Darvin\MenuBundle\Builder;


use Knp\Menu\ItemInterface;

/**
 * Builder
 */
interface MenuBuilderInterface
{
    public const BUILD_METHOD = 'buildMenu';
    
    /**
     * @param array $options Options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu(array $options = []): ItemInterface;
}