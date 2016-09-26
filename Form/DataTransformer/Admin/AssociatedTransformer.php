<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\DataTransformer\Admin;

use Darvin\MenuBundle\Association\Associated;
use Darvin\MenuBundle\Configuration\AssociationConfiguration;
use Darvin\MenuBundle\Form\Type\Admin\AssociatedType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Associated admin data transformer
 */
class AssociatedTransformer implements DataTransformerInterface
{
    /**
     * @var \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private $associationConfig;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;

    /**
     * @param \Darvin\MenuBundle\Configuration\AssociationConfiguration $associationConfig Association configuration
     * @param \Doctrine\Common\Persistence\ObjectManager                $om                Object manager
     */
    public function __construct(AssociationConfiguration $associationConfig, ObjectManager $om)
    {
        $this->associationConfig = $associationConfig;
        $this->om = $om;
    }

    /**
     * @param \Darvin\MenuBundle\Association\Associated $associated Associated
     *
     * @return array
     */
    public function transform($associated)
    {
        $class = $associated->getClass();

        if (empty($class)) {
            return null;
        }

        $association = $this->associationConfig->getAssociationByClass($class);

        $transformed = [
            'alias' => $association->getAlias(),
        ];

        if (null !== $associated->getId()) {
            $transformed[AssociatedType::ENTITY_FIELD_PREFIX.$association->getAlias()] = $this->om->find(
                $associated->getClass(),
                $associated->getId()
            );
        }

        return $transformed;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value['alias'])) {
            return null;
        }

        $association = $this->associationConfig->getAssociationByAlias($value['alias']);

        $associated = new Associated($association->getClass());

        $object = $value[AssociatedType::ENTITY_FIELD_PREFIX.$value['alias']];

        if (empty($object)) {
            return $associated;
        }

        $ids = $this->om->getClassMetadata($association->getClass())->getIdentifierValues($object);

        return $associated->setId(reset($ids));
    }
}
