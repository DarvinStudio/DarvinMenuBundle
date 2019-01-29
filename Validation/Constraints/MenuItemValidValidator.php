<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Validation\Constraints;

use Darvin\MenuBundle\Entity\Menu\Item;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Menu item valid constraint validator
 */
class MenuItemValidValidator extends ConstraintValidator
{
    /**
     * Checks if the passed menu item is valid.
     *
     * @param \Darvin\MenuBundle\Entity\Menu\Item                                                             $menuItem   Menu item that should be validated
     * @param \Darvin\MenuBundle\Validation\Constraints\MenuItemValid|\Symfony\Component\Validator\Constraint $constraint Menu item valid validation constraint
     *
     * @throws \InvalidArgumentException
     */
    public function validate($menuItem, Constraint $constraint)
    {
        if (!$menuItem instanceof Item) {
            $message = sprintf(
                'Validated menu item must be instance of "%s", got instance of "%s".',
                Item::class,
                get_class($menuItem)
            );

            throw new \InvalidArgumentException($message);
        }
        if (null !== $menuItem->getSlugMapItem()) {
            return;
        }
        foreach ($menuItem->getTranslations() as $translation) {
            $title = $translation->getTitle();

            if (empty($title)) {
                $this->context->buildViolation($constraint->message)->addViolation();

                return;
            }
        }
    }
}
