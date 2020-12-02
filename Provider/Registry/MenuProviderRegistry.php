<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Provider\Registry;

use Darvin\MenuBundle\Provider\Model\Menu;
use Darvin\MenuBundle\Provider\Provider\MenuProviderInterface;

/**
 * Menu provider registry
 */
class MenuProviderRegistry implements MenuProviderRegistryInterface
{
    /**
     * @var \Darvin\MenuBundle\Provider\Provider\MenuProviderInterface[]
     */
    private $providers;

    /**
     * @var \Darvin\MenuBundle\Provider\Model\Menu[]|null
     */
    private $menuCollection;

    /**
     * Menu provider registry constructor.
     */
    public function __construct()
    {
        $this->providers = [];

        $this->menuCollection = null;
    }

    /**
     * @param \Darvin\MenuBundle\Provider\Provider\MenuProviderInterface $provider Menu provider
     */
    public function addProvider(MenuProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function getMenu(string $name): Menu
    {
        if (!$this->exists($name)) {
            throw new \InvalidArgumentException(sprintf('Menu "%s" does not exist.', $name));
        }

        return $this->getMenuCollection()[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $name): bool
    {
        $collection = $this->getMenuCollection();

        return isset($collection[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getMenuCollection(): array
    {
        if (null === $this->menuCollection) {
            $menuCollection = [];

            foreach ($this->providers as $provider) {
                foreach ($provider->getMenus() as $menu) {
                    if (isset($menuCollection[$menu->getName()])) {
                        throw new \LogicException(sprintf('Menu "%s" already exists.', $menu->getName()));
                    }

                    $menuCollection[$menu->getName()] = $menu;
                }
            }

            $this->menuCollection = $menuCollection;
        }

        return $this->menuCollection;
    }
}
