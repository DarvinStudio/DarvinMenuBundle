<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DependencyInjection\Compiler;

use Darvin\MenuBundle\Builder\Builder;
use Knp\Bundle\MenuBundle\DependencyInjection\Compiler\MenuPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Create menu services compiler pass
 */
class CreateMenuServicesPass implements CompilerPassInterface
{
    const PARENT_ID = 'darvin_menu.menu.abstract';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definitions = [];

        foreach ($this->getMenuConfig($container)->getMenus() as $menu) {
            $definitions[$menu->getMenuServiceId()] = (new DefinitionDecorator(self::PARENT_ID))
                ->setFactory([new Reference($menu->getBuilderId()), Builder::BUILD_METHOD])
                ->addTag('knp_menu.menu', [
                    'alias' => $menu->getMenuServiceAlias(),
                ]);
        }
        foreach ($definitions as $id => $definition) {
            if ($container->hasDefinition($id)) {
                throw new \RuntimeException(sprintf('Service "%s" already exists. Please change menu alias.', $id));
            }
        }

        $container->addDefinitions($definitions);

        (new MenuPass())->process($container);
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
