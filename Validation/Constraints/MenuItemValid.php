<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Menu item valid validation constraint
 *
 * @Annotation
 */
class MenuItemValid extends Constraint
{
    /**
     * @var string
     */
    public $message = 'menu_item.invalid';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
