<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Builder;

use Knp\Menu\FactoryInterface;

/**
 * Builder
 */
class Builder
{
    const BUILD_METHOD = 'buildMenu';

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $itemFactory;

    /**
     * @var string
     */
    private $menuAlias;

    /**
     * @param \Knp\Menu\FactoryInterface $itemFactory Item factory
     * @param string                     $menuAlias   Menu alias
     */
    public function __construct(FactoryInterface $itemFactory, $menuAlias)
    {
        $this->itemFactory = $itemFactory;
        $this->menuAlias = $menuAlias;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu()
    {
        $root = $this->itemFactory->createItem('root');
        $root->addChild($this->itemFactory->createItem($this->menuAlias));

        return $root;
    }
}
