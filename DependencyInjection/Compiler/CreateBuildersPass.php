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
    const PARENT_ID             = 'darvin_menu.abstract_builder';
    const PARENT_ID_BREADCRUMBS = 'darvin_menu.abstract_breadcrumbs_builder';

    const TAG_BUILDER = 'darvin_menu.builder';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definitions = [];

        foreach ($this->getMenuConfig($container)->getMenus() as $menu) {
            $definitions[$menu->getBuilderId()] = $this->buildDefinition(self::PARENT_ID, $menu->getAlias(), $menu->getBuilderAlias());

            if ($menu->isBreadcrumbsEnabled()) {
                $definitions[$menu->getBreadcrumbsBuilderId()] = $this->buildDefinition(
                    self::PARENT_ID_BREADCRUMBS,
                    $menu->getAlias(),
                    $menu->getBreadcrumbsBuilderAlias()
                );
            }
        }
        foreach ($definitions as $id => $definition) {
            if ($container->hasDefinition($id)) {
                throw new \RuntimeException(sprintf('Service "%s" already exists. Please change menu alias.', $id));
            }
        }

        $container->addDefinitions($definitions);
    }

    /**
     * @param string $parentId     Parent service ID
     * @param string $menuAlias    Menu alias
     * @param string $builderAlias Builder alias
     *
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    private function buildDefinition($parentId, $menuAlias, $builderAlias)
    {
        return (new DefinitionDecorator($parentId))
            ->addArgument($menuAlias)
            ->addTag('knp_menu.menu_builder', [
                'method' => Builder::BUILD_METHOD,
                'alias'  => $builderAlias,
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
