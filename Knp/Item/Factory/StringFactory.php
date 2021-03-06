<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Knp\Item\Factory;

/**
 * String KNP menu item factory
 */
class StringFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function supports($source): bool
    {
        return is_string($source);
    }

    /**
     * {@inheritDoc}
     */
    protected function nameItem($source): ?string
    {
        return $source;
    }
}
