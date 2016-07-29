<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle;

use Darvin\MenuBundle\DependencyInjection\Compiler\CreateBuildersPass;
use Knp\Bundle\MenuBundle\DependencyInjection\Compiler\MenuBuilderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Menu bundle
 */
class DarvinMenuBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new CreateBuildersPass())
            ->addCompilerPass(new MenuBuilderPass());
    }
}
