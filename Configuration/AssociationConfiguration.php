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
 * Association configuration
 */
class AssociationConfiguration
{
    /**
     * @var \Darvin\MenuBundle\Configuration\Association[]
     */
    private $associations;

    /**
     * @param array $configs Configs
     */
    public function __construct(array $configs)
    {
        $this->associations = [];

        foreach ($configs as $config) {
            $alias = $config['alias'];
            $this->associations[$alias] = new Association($alias, $config['class'], $config['route']['name'], $config['route']['params']);
        }
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\Association[]
     */
    public function getAssociations()
    {
        return $this->associations;
    }
}
