<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DependencyInjection\Compiler;

use Darvin\MenuBundle\DependencyInjection\DarvinMenuExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register KNP menu item factories compiler pass
 */
class RegisterKnpItemFactoriesPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition('darvin_menu.knp.item.factory_registry');

        foreach (array_keys($container->findTaggedServiceIds(DarvinMenuExtension::TAG_KNP_ITEM_FACTORY)) as $id) {
            $registry->addMethodCall('addFactory', [new Reference($id)]);
        }
    }
}
