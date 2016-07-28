<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Configuration;

/**
 * Menu object configuration
 */
class MenuObjectConfiguration
{
    /**
     * @var \Darvin\MenuBundle\Configuration\MenuObject[]
     */
    private $menuObjects;

    /**
     * @param array $configs Configs
     */
    public function __construct(array $configs)
    {
        $this->menuObjects = [];

        foreach ($configs as $config) {
            $this->menuObjects[] = new MenuObject($config['alias'], $config['class'], $config['route']['name'], $config['route']['params']);
        }
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\MenuObject[]
     */
    public function getMenuObjects()
    {
        return $this->menuObjects;
    }
}
