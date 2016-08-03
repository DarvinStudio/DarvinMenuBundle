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
use Darvin\MenuBundle\Configuration\Menu;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Create builders compiler pass
 */
class CreateBuildersPass implements CompilerPassInterface
{
    const BREADCRUMBS_ALIAS_PREFIX = 'darvin_breadcrumbs_';
    const BREADCRUMBS_ID_PREFIX    = 'darvin_menu.breadcrumbs_builder.';
    const BREADCRUMBS_PARENT_ID    = 'darvin_menu.abstract_breadcrumbs_builder';

    const GENERIC_ALIAS_PREFIX = 'darvin_menu_';
    const GENERIC_ID_PREFIX    = 'darvin_menu.builder.';
    const GENERIC_PARENT_ID    = 'darvin_menu.abstract_builder';

    const TAG_BUILDER = 'darvin_menu.builder';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definitions = [];

        foreach ($this->getMenuConfig($container)->getMenus() as $menu) {
            $definitions[self::GENERIC_ID_PREFIX.$menu->getAlias()] = $this->buildDefinition(
                self::GENERIC_PARENT_ID,
                $menu,
                self::GENERIC_ALIAS_PREFIX
            );

            if ($menu->isBreadcrumbsEnabled()) {
                $definitions[self::BREADCRUMBS_ID_PREFIX.$menu->getAlias()] = $this->buildDefinition(
                    self::BREADCRUMBS_PARENT_ID,
                    $menu,
                    self::BREADCRUMBS_ALIAS_PREFIX
                );
            }
        }

        $container->addDefinitions($definitions);
    }

    /**
     * @param string                                $parentId    Parent service ID
     * @param \Darvin\MenuBundle\Configuration\Menu $menu        Menu
     * @param string                                $aliasPrefix Alias prefix
     *
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    private function buildDefinition($parentId, Menu $menu, $aliasPrefix)
    {
        return (new DefinitionDecorator($parentId))
            ->addArgument($menu->getAlias())
            ->addTag('knp_menu.menu_builder', [
                'method' => Builder::BUILD_METHOD,
                'alias'  => $aliasPrefix.$menu->getAlias(),
            ])
            ->addTag(self::TAG_BUILDER);
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
