<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item\Pool;

use Darvin\MenuBundle\Item\ItemFactoryInterface;

/**
 * Item factory pool
 */
class ItemFactoryPool implements ItemFactoryPoolInterface
{
    /**
     * @var \Darvin\MenuBundle\Item\ItemFactoryInterface[]
     */
    private $factories;

    /**
     * Item factory pool constructor.
     */
    public function __construct()
    {
        $this->factories = [];
    }

    /**
     * @param \Darvin\MenuBundle\Item\ItemFactoryInterface $factory Item factory
     */
    public function addFactory(ItemFactoryInterface $factory): void
    {
        $this->factories[] = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function getFactory($source): ItemFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($source)) {
                return $factory;
            }
        }

        throw new \InvalidArgumentException('Unable to find any suitable item factory.');
    }
}
