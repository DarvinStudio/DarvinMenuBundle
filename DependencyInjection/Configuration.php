<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('darvin_menu');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->arrayNode('breadcrumbs')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('template')->defaultValue('DarvinMenuBundle::breadcrumbs.html.twig')->end()
                        ->scalarNode('slug_parameter_name')->defaultValue('slug')->end()
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
                ->arrayNode('menus')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('alias')->isRequired()->cannotBeEmpty()->end()
                            ->booleanNode('breadcrumbs')->defaultTrue()->end()
                            ->scalarNode('icon')->defaultValue('bundles/darvinmenu/images/admin/menu_main.png')->end()
                            ->arrayNode('build_options')->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('build_hidden_slugmap_children')->defaultFalse()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
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
                    $menuAliases = array_map(function (array $menu) {
                        return $menu['alias'];
                    }, $config['menus']);

                    foreach ($config['switcher']['default_menus'] as $defaultMenuAliases) {
                        foreach ($defaultMenuAliases as $defaultMenuAlias) {
                            if (!in_array($defaultMenuAlias, $menuAliases)) {
                                throw new \RuntimeException(
                                    sprintf('Menu "%s" does not defined in the "menus" section.', $defaultMenuAlias)
                                );
                            }
                        }
                    }

                    return false;
                })
                ->thenInvalid(null);

        return $treeBuilder;
    }
}
