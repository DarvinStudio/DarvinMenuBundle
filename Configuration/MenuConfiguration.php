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
     * @param array[] $configs Configs
     *
     * @throws \Darvin\MenuBundle\Configuration\ConfigurationException
     */
    public function __construct(array $configs)
    {
        $this->menus = [];

        foreach ($configs as $config) {
            $alias = $config['alias'];

            if (isset($this->menus[$alias])) {
                throw new ConfigurationException(sprintf('Menu with alias "%s" already exists.', $alias));
            }

            $this->menus[$alias] = new Menu($alias, $config['breadcrumbs'], $config['icon'], $config['build_options']);
        }
    }

    /**
     * @param string $alias Menu alias
     *
     * @return \Darvin\MenuBundle\Configuration\Menu
     * @throws \Darvin\MenuBundle\Configuration\ConfigurationException
     */
    public function getMenu($alias)
    {
        if (!isset($this->menus[$alias])) {
            throw new ConfigurationException(sprintf('Menu with alias "%s" does not exist.', $alias));
        }

        return $this->menus[$alias];
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\Menu[]
     */
    public function getMenus()
    {
        return $this->menus;
    }
}
