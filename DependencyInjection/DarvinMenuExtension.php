<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DependencyInjection;

use Darvin\MenuBundle\Knp\Item\Factory\KnpItemFactoryInterface;
use Darvin\Utils\DependencyInjection\ConfigInjector;
use Darvin\Utils\DependencyInjection\ConfigLoader;
use Darvin\Utils\DependencyInjection\ExtensionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DarvinMenuExtension extends Extension implements PrependExtensionInterface
{
    public const TAG_KNP_ITEM_FACTORY = 'darvin_menu.knp.item.factory';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(KnpItemFactoryInterface::class)->addTag(self::TAG_KNP_ITEM_FACTORY);

        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['KnpMenuBundle'])) {
            throw new \RuntimeException('Please enable "KnpMenuBundle".');
        }

        $config = $this->processConfiguration(new Configuration(), $configs);

        (new ConfigInjector($container))->inject($config, $this->getAlias());

        $container->setParameter(
            'darvin_menu.breadcrumbs.slug_parameter_name',
            $config['breadcrumbs']['slug_parameter_name']
        );

        (new ConfigLoader($container, __DIR__.'/../Resources/config/services'))->load([
            'admin',
            'breadcrumbs',
            'builder',
            'configuration',
            'controller',
            'knp',
            'matcher',
            'provider',
            'renderer',
            'switcher',

            'dev/fixture' => ['env' => 'dev'],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        (new ExtensionConfigurator($container, __DIR__.'/../Resources/config/app'))->configure([
            'darvin_admin',
            'darvin_utils',
            'doctrine',
            'knp_menu',
            'twig',
        ]);
    }
}
