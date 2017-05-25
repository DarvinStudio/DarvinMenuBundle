<?php
/**
 * Created by PhpStorm.
 * User: levsemin
 * Date: 25.04.17
 * Time: 23:06
 */

namespace Darvin\MenuBundle\Builder;


/**
 * Builder
 */
interface MenuBuilderInterface
{
    const BUILD_METHOD = 'buildMenu';   
    
    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu();

    /**
     * Set menu alias value
     *
     * @param $menuAlias
     * @param array $buildOptions
     * @return void
     */
    public function setMenuAlias($menuAlias, array $buildOptions = []);
}