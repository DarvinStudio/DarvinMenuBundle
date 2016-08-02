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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add item factories to builders pass
 */
class AddItemFactoriesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $references = [];

        foreach ($this->getAssociationConfiguration($container)->getAssociations() as $association) {
            $references[$association->getClass()] = new Reference($association->getItemFactory());
        }
        if (empty($references)) {
            return;
        }
        foreach ($container->findTaggedServiceIds(CreateBuildersPass::TAG_BUILDER) as $id => $attr) {
            $builderDefinition = $container->getDefinition($id);

            foreach ($references as $associationClass => $reference) {
                $builderDefinition->addMethodCall('addItemFactory', [
                    $associationClass,
                    $reference,
                ]);
            }
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     *
     * @return \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private function getAssociationConfiguration(ContainerInterface $container)
    {
        return $container->get('darvin_menu.configuration.association');
    }
}
