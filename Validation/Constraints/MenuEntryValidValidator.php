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

use Darvin\MenuBundle\Entity\MenuEntry;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Menu entry valid constraint validator
 */
class MenuEntryValidValidator extends ConstraintValidator
{
    /**
     * Checks if the passed menu entry is valid.
     *
     * @param \Darvin\MenuBundle\Entity\MenuEntry                                                              $entry      Menu entry that should be validated
     * @param \Darvin\MenuBundle\Validation\Constraints\MenuEntryValid|\Symfony\Component\Validator\Constraint $constraint Menu entry valid validation constraint
     *
     * @throws \InvalidArgumentException
     */
    public function validate($entry, Constraint $constraint): void
    {
        if (!$entry instanceof MenuEntry) {
            $message = sprintf(
                'Validated menu entry must be instance of "%s", got instance of "%s".',
                MenuEntry::class,
                ClassUtils::getClass($entry)
            );

            throw new \InvalidArgumentException($message);
        }
        if (null !== $entry->getSlugMapItem()) {
            return;
        }
        foreach ($entry->getTranslations() as $translation) {
            $title = $translation->getTitle();
            $url   = $translation->getUrl();

            if (null === $title && null === $url) {
                $this->context->buildViolation($constraint->message)->addViolation();

                return;
            }
        }
    }
}
