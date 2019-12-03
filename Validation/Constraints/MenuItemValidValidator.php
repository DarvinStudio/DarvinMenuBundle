<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Validation\Constraints;

use Darvin\MenuBundle\Entity\Menu\Item;
use Doctrine\Common\Util\ClassUtils;
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
    public function validate($menuItem, Constraint $constraint): void
    {
        if (!$menuItem instanceof Item) {
            $message = sprintf(
                'Validated menu item must be instance of "%s", got instance of "%s".',
                Item::class,
                ClassUtils::getClass($menuItem)
            );

            throw new \InvalidArgumentException($message);
        }
        if (null !== $menuItem->getSlugMapItem()) {
            return;
        }
        foreach ($menuItem->getTranslations() as $translation) {
            $title = $translation->getTitle();
            $url   = $translation->getUrl();

            if (null === $title && null === $url) {
                $this->context->buildViolation($constraint->message)->addViolation();

                return;
            }
        }
    }
}
