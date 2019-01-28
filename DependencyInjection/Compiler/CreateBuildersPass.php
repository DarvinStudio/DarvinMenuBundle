<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DependencyInjection\Compiler;

use Darvin\MenuBundle\Builder\MenuBuilderInterface;
use Darvin\MenuBundle\Configuration\MenuConfigurationInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create builders compiler pass
 */
class CreateBuildersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definitions = [];

        foreach ($this->getMenuConfig($container)->getMenus() as $menu) {
            $id = $menu->getBuilderId();

            if ($container->hasDefinition($id)) {
                $class = new \ReflectionClass($container->getDefinition($id)->getClass());

                if (!$class->implementsInterface(MenuBuilderInterface::class)) {
                    throw new \RuntimeException(sprintf(
                        'Service "%s" already exists and it\'s not instance of MenuBuilderInterface. Please change the menu alias.', 
                        $id
                    ));
                }

                continue;
            }

            $definition = new ChildDefinition('darvin_menu.builder.abstract');
            $definition->addMethodCall('setMenuAlias', [
                $menu->getAlias(),
                $menu->getBuilderOptions(),
            ]);

            $definitions[$id] = $definition;
        }

        $container->addDefinitions($definitions);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     *
     * @return \Darvin\MenuBundle\Configuration\MenuConfigurationInterface
     */
    private function getMenuConfig(ContainerInterface $container): MenuConfigurationInterface
    {
        return $container->get('darvin_menu.configuration.menu');
    }
}
