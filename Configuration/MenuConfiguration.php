<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Configuration;

/**
 * Menu configuration
 */
class MenuConfiguration implements MenuConfigurationInterface
{
    /**
     * @var array
     */
    private $configs;

    /**
     * @var \Darvin\MenuBundle\Configuration\Menu[]|null
     */
    private $menus;

    /**
     * @param array $configs Configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;

        $this->menus = null;

    }

    /**
     * {@inheritDoc}
     */
    public function getMenu(string $name): Menu
    {
        $menus = $this->getMenus();

        if (!isset($menus[$name])) {
            throw new \InvalidArgumentException(sprintf('Menu "%s" does not exist.', $name));
        }

        return $menus[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMenu(string $name): bool
    {
        $menus = $this->getMenus();

        return isset($menus[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getMenus(): array
    {
        if (null === $this->menus) {
            $menus = [];

            foreach ($this->configs as $name => $config) {
                if (isset($menus[$name])) {
                    throw new \LogicException(sprintf('Menu "%s" already exists.', $name));
                }

                $menus[$name] = new Menu($name);
            }

            $this->menus = $menus;
        }

        return $this->menus;
    }
}
