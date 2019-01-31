<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DependencyInjection;

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
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
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

        (new ConfigLoader($container, __DIR__.'/../Resources/config'))->load([
            'admin',
            'breadcrumbs',
            'builder',
            'configuration',
            'item_factory',
            'matcher',
            'provider',
            'slug_map',
            'switcher',

            'dev/fixture' => ['env' => 'dev'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        (new ExtensionConfigurator($container, __DIR__.'/../Resources/config/app'))->configure([
            'darvin_admin',
            'darvin_image',
            'knp_menu',
            'twig',
        ]);
    }
}
