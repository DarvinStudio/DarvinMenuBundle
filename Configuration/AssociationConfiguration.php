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
    private $associationByAliases;

    /**
     * @var \Darvin\MenuBundle\Configuration\Association[]
     */
    private $associationByClasses;

    /**
     * @param array $configs Configs
     */
    public function __construct(array $configs)
    {
        $this->associationByAliases = $this->associationByClasses = [];

        foreach ($configs as $config) {
            $alias = $config['alias'];
            $class = $config['class'];

            $association = new Association($alias, $class, $config['route']['name'], $config['route']['params']);

            $this->associationByAliases[$alias] = $association;
            $this->associationByClasses[$class] = $association;
        }
    }

    /**
     * @param string $alias Alias
     *
     * @return \Darvin\MenuBundle\Configuration\Association
     */
    public function getAssociationByAlias($alias)
    {
        return $this->associationByAliases[$alias];
    }

    /**
     * @param string $class Class
     *
     * @return \Darvin\MenuBundle\Configuration\Association
     */
    public function getAssociationByClass($class)
    {
        return $this->associationByClasses[$class];
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\Association[]
     */
    public function getAssociations()
    {
        return $this->associationByAliases;
    }
}
