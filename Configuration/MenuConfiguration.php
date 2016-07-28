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
 * Menu configuration
 */
class MenuConfiguration
{
    /**
     * @var \Darvin\MenuBundle\Configuration\Menu[]
     */
    private $menus;

    /**
     * @param string[] $menuLabels Menu labels
     */
    public function __construct(array $menuLabels)
    {
        foreach ($menuLabels as $label) {
            $this->menus[] = new Menu($label);
        }
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\Menu[]
     */
    public function getMenus()
    {
        return $this->menus;
    }
}
