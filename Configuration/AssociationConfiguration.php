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

use Darvin\AdminBundle\EntityNamer\EntityNamerInterface;

/**
 * Association configuration
 */
class AssociationConfiguration
{
    /**
     * @var \Darvin\AdminBundle\EntityNamer\EntityNamerInterface
     */
    private $entityNamer;

    /**
     * @var \Darvin\MenuBundle\Configuration\Association[]
     */
    private $associationByAliases;

    /**
     * @var \Darvin\MenuBundle\Configuration\Association[]
     */
    private $associationByClasses;

    /**
     * @param \Darvin\AdminBundle\EntityNamer\EntityNamerInterface $entityNamer Entity namer
     * @param array[]                                              $configs     Configs
     *
     * @throws \Darvin\MenuBundle\Configuration\ConfigurationException
     */
    public function __construct(EntityNamerInterface $entityNamer, array $configs)
    {
        $this->entityNamer = $entityNamer;

        $this->associationByAliases = $this->associationByClasses = [];

        foreach ($configs as $config) {
            $class = $config['class'];
            $alias = !empty($config['alias']) ? $config['alias'] : $this->entityNamer->name($class);

            if (isset($this->associationByAliases[$alias])) {
                throw new ConfigurationException(sprintf('Association with alias "%s" already exists.', $alias));
            }
            if (isset($this->associationByClasses[$class])) {
                throw new ConfigurationException(sprintf('Association with class "%s" already exists.', $class));
            }

            $association = new Association($alias, $class, $config['form_type'], $config['route']['name'], $config['route']['params']);

            $this->associationByAliases[$alias] = $association;
            $this->associationByClasses[$class] = $association;
        }
    }

    /**
     * @param string $alias Alias
     *
     * @return \Darvin\MenuBundle\Configuration\Association
     * @throws \Darvin\MenuBundle\Configuration\ConfigurationException
     */
    public function getAssociationByAlias($alias)
    {
        if (!isset($this->associationByAliases[$alias])) {
            throw new ConfigurationException(sprintf('Unable to find association by alias "%s".', $alias));
        }

        return $this->associationByAliases[$alias];
    }

    /**
     * @param string $class Class
     *
     * @return \Darvin\MenuBundle\Configuration\Association
     * @throws \Darvin\MenuBundle\Configuration\ConfigurationException
     */
    public function getAssociationByClass($class)
    {
        if (!isset($this->associationByClasses[$class])) {
            throw new ConfigurationException(sprintf('Unable to find association by class "%s".', $class));
        }

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
