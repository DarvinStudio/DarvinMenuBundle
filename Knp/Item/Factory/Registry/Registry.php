<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Knp\Item\Factory\Registry;

use Darvin\MenuBundle\Knp\Item\Factory\KnpItemFactoryInterface;
use Knp\Menu\ItemInterface;

/**
 * KNP menu item factory registry
 */
class Registry implements KnpItemFactoryRegistryInterface
{
    /**
     * @var \Darvin\MenuBundle\Knp\Item\Factory\KnpItemFactoryInterface[]
     */
    private $factories;

    /**
     * KNP menu item factory registry constructor.
     */
    public function __construct()
    {
        $this->factories = [];
    }

    /**
     * @param \Darvin\MenuBundle\Knp\Item\Factory\KnpItemFactoryInterface $factory KNP menu item factory
     */
    public function addFactory(KnpItemFactoryInterface $factory): void
    {
        $this->factories[] = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function createItem($source): ItemInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($source)) {
                return $factory->createItem($source);
            }
        }

        throw new \InvalidArgumentException('Unable to find any suitable KNP menu item factory.');
    }
}
