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

use Darvin\MenuBundle\Builder\Builder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Create builders compiler pass
 */
class CreateBuildersPass implements CompilerPassInterface
{
    const ALIAS_PREFIX = 'darvin_menu_';

    const ID_PREFIX = 'darvin_menu.builder.';

    const PARENT_ID = 'darvin_menu.abstract_builder';

    const TAG_BUILDER = 'darvin_menu.builder';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definitions = [];

        foreach ($this->getMenuConfiguration($container)->getMenus() as $menu) {
            $definitions[self::ID_PREFIX.$menu->getAlias()] = (new DefinitionDecorator(self::PARENT_ID))
                ->addArgument($menu->getAlias())
                ->addTag('knp_menu.menu_builder', [
                    'method' => Builder::BUILD_METHOD,
                    'alias'  => self::ALIAS_PREFIX.$menu->getAlias(),
                ])
                ->addTag(self::TAG_BUILDER);
        }

        $container->addDefinitions($definitions);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     *
     * @return \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private function getMenuConfiguration(ContainerInterface $container)
    {
        return $container->get('darvin_menu.configuration.menu');
    }
}
