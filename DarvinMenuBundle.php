<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle;

use Darvin\MenuBundle\DependencyInjection\Compiler\CreateBuildersPass;
use Darvin\MenuBundle\DependencyInjection\Compiler\CreateMenuServicesPass;
use Darvin\MenuBundle\DependencyInjection\Compiler\OverrideMatcherPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Menu bundle
 */
class DarvinMenuBundle extends Bundle
{
    const MAJOR_VERSION = 6;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new CreateBuildersPass())
            ->addCompilerPass(new CreateMenuServicesPass())
            ->addCompilerPass(new OverrideMatcherPass());
    }
}
