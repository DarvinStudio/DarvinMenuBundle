<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Validator\Menu;

use Darvin\MenuBundle\Configuration\AssociationConfiguration;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Validator\ValidatorException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Unique menu item validator
 */
class UniqueItemValidator extends UniqueEntityValidator
{
    /**
     * @var \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private $associationConfig;

    /**
     * @param \Darvin\MenuBundle\Configuration\AssociationConfiguration $associationConfig Association configuration
     */
    public function setAssociationConfig(AssociationConfiguration $associationConfig)
    {
        $this->associationConfig = $associationConfig;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item                                                                 $item       Menu item
     * @param \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity|\Symfony\Component\Validator\Constraint $constraint Constraint
     *
     * @throws \Darvin\MenuBundle\Validator\ValidatorException
     */
    public function validate($item, Constraint $constraint)
    {
        if (!$item instanceof Item) {
            throw new ValidatorException(
                sprintf('Validated entity must be instance of "%s", got instance of "%s".', Item::ITEM_CLASS, get_class($item))
            );
        }

        $associatedClass = $item->getAssociatedClass();

        if (!empty($associatedClass)) {
            $constraint->errorPath = sprintf(
                'associated[associated_%s]',
                $this->associationConfig->getAssociationByClass($associatedClass)->getAlias()
            );
        }

        parent::validate($item, $constraint);
    }
}
