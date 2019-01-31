<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Create menu services compiler pass
 */
class CreateMenuServicesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definitions = [];

        foreach ($this->getMenuConfig($container)->getMenus() as $menu) {
            $id = $menu->getMenuServiceId();

            if ($container->hasDefinition($id)) {
                throw new \RuntimeException(sprintf('Service "%s" already exists. Please change menu alias.', $id));
            }

            $definition = new ChildDefinition('darvin_menu.menu.abstract');
            $definition->setFactory([new Reference($menu->getBuilderId()), MenuBuilderInterface::BUILD_METHOD]);
            $definition->addTag('knp_menu.menu', [
                'alias' => $menu->getMenuServiceAlias(),
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
