<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DependencyInjection\Compiler;

use Darvin\MenuBundle\Builder\MenuBuilderInterface;
use Knp\Bundle\MenuBundle\DependencyInjection\Compiler\MenuBuilderPass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create builders compiler pass
 */
class CreateBuildersPass implements CompilerPassInterface
{
    const PARENT_ID = 'darvin_menu.builder.abstract';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
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
            $definitions[$id] = (new ChildDefinition(self::PARENT_ID))->addMethodCall(
                'setMenuAlias', 
                [$menu->getAlias(), $menu->getBuilderOptions()]
            );
        }

        $container->addDefinitions($definitions);
        (new MenuBuilderPass())->process($container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     *
     * @return \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private function getMenuConfig(ContainerInterface $container)
    {
        return $container->get('darvin_menu.configuration.menu');
    }
}
