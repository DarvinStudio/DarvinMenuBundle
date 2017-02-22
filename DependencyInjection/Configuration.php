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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                ->arrayNode('menus')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('alias')->isRequired()->cannotBeEmpty()->end()
                            ->booleanNode('breadcrumbs')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('associations')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('alias')->defaultNull()->end()
                            ->scalarNode('class')->isRequired()->cannotBeEmpty()
                                ->validate()
                                    ->ifTrue(function ($class) {
                                        return !class_exists($class);
                                    })
                                    ->thenInvalid('Association class %s does not exist.')
                                ->end()
                            ->end()
                            ->scalarNode('item_factory')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('hide_property')->defaultNull()->end()
                            ->scalarNode('form_type')->defaultValue(EntityType::class);

        return $treeBuilder;
    }
}
