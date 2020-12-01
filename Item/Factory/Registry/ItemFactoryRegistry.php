<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item\Factory\Registry;

use Darvin\MenuBundle\Item\Factory\ItemFactoryInterface;
use Knp\Menu\ItemInterface;

/**
 * Item factory registry
 */
class ItemFactoryRegistry implements ItemFactoryRegistryInterface
{
    /**
     * @var \Darvin\MenuBundle\Item\Factory\ItemFactoryInterface[]
     */
    private $factories;

    /**
     * Item factory registry constructor.
     */
    public function __construct()
    {
        $this->factories = [];
    }

    /**
     * @param \Darvin\MenuBundle\Item\Factory\ItemFactoryInterface $factory Item factory
     */
    public function addFactory(ItemFactoryInterface $factory): void
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

        throw new \InvalidArgumentException('Unable to find any suitable item factory.');
    }
}
