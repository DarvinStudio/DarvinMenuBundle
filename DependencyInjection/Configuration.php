<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('darvin_menu');

        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $root */
        $root = $builder->getRootNode();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $root
            ->children()
                ->arrayNode('breadcrumbs')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('template')->defaultValue('@DarvinMenu/breadcrumbs.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('slug_parameter_name')->defaultValue('slug')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('entities')->useAttributeAsKey('entity')
                    ->prototype('array')
                        ->children()
                            ->booleanNode('admin')->defaultTrue()->info('Whether to allow to add entities to menu in admin panel')->end()
                            ->booleanNode('slug_children')->defaultTrue()->info('Whether to show entities in slug map children')->end()
                        ->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $entities) {
                            foreach (array_keys($entities) as $entity) {
                                if (!class_exists($entity)) {
                                    throw new \RuntimeException(sprintf('Entity class "%s" does not exist.', $entity));
                                }
                            }
                        })
                        ->thenInvalid(null)
                    ->end()
                ->end()
                ->arrayNode('menus')->useAttributeAsKey('alias')->prototype('array')->end()->end()
                ->arrayNode('switcher')->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default_menus')->useAttributeAsKey('entity')
                            ->prototype('array')
                                ->prototype('scalar')->cannotBeEmpty()->end()
                                ->beforeNormalization()->castToArray()->end()
                                ->requiresAtLeastOneElement()
                            ->end()
                            ->validate()
                                ->ifTrue(function (array $defaultMenus) {
                                    foreach (array_keys($defaultMenus) as $entity) {
                                        if (!class_exists($entity) && !interface_exists($entity)) {
                                            throw new \RuntimeException(
                                                sprintf('Entity class or interface "%s" does not exist.', $entity)
                                            );
                                        }
                                    }

                                    return false;
                                })
                                ->thenInvalid(null)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function (array $config) {
                    foreach ($config['switcher']['default_menus'] as $aliases) {
                        foreach ($aliases as $alias) {
                            if (!isset($config['menus'][$alias])) {
                                throw new \RuntimeException(sprintf('Menu "%s" does not defined in the "menus" section.', $alias));
                            }
                        }
                    }

                    return false;
                })
                ->thenInvalid(null);

        return $builder;
    }
}
