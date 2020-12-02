<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle;

use Darvin\MenuBundle\DependencyInjection\Compiler\OverrideMatcherPass;
use Darvin\MenuBundle\DependencyInjection\Compiler\RegisterKnpItemFactoriesPass;
use Darvin\MenuBundle\DependencyInjection\Compiler\RegisterProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Menu bundle
 */
class DarvinMenuBundle extends Bundle
{
    public const MAJOR_VERSION = 8;

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new OverrideMatcherPass())
            ->addCompilerPass(new RegisterKnpItemFactoriesPass())
            ->addCompilerPass(new RegisterProvidersPass());
    }
}
