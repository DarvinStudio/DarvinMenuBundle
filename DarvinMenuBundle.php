<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle;

use Darvin\MenuBundle\DependencyInjection\Compiler\AddItemFactoriesPass;
use Darvin\MenuBundle\DependencyInjection\Compiler\CreateBuildersPass;
use Darvin\MenuBundle\DependencyInjection\Compiler\CreateMenuServicesPass;
use Darvin\MenuBundle\DependencyInjection\Compiler\OverrideMatcherPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Menu bundle
 */
class DarvinMenuBundle extends Bundle
{
    public const MAJOR_VERSION = 7;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AddItemFactoriesPass())
            ->addCompilerPass(new CreateBuildersPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 20)
            ->addCompilerPass(new CreateMenuServicesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10)
            ->addCompilerPass(new OverrideMatcherPass());
    }
}
