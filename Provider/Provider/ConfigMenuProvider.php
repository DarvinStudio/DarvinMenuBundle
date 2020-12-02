<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Provider\Provider;

use Darvin\MenuBundle\Provider\Model\Menu;

/**
 * Config menu provider
 */
class ConfigMenuProvider implements MenuProviderInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config Config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getMenus(): iterable
    {
        foreach (array_keys($this->config) as $name) {
            yield new Menu($name);
        }
    }
}
