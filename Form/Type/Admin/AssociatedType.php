<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Darvin\MenuBundle\Configuration\AssociationConfiguration;
use Darvin\MenuBundle\Form\DataTransformer\Admin\AssociatedTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Admin associated form type
 */
class AssociatedType extends AbstractType
{
    const ENTITY_FIELD_PREFIX = 'associated_';

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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('alias', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices'           => $this->buildAliasChoices(),
                'choices_as_values' => true,
                'constraints'       => new NotBlank(),
                'attr'              => [
                   'class' => 'alias',
                ],
            ])
            ->addModelTransformer(new AssociatedTransformer($this->associationConfig, $this->om));

        foreach ($this->associationConfig->getAssociations() as $association) {
            $builder->add(self::ENTITY_FIELD_PREFIX.$association->getAlias(), $association->getFormType(), [
                'label' => $association->getTitle(),
                'class' => $association->getClass(),
                'attr'  => [
                    'class'        => 'slave_input',
                    'data-master'  => '.alias',
                    'data-show-on' => $association->getAlias(),
                ],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_menu_admin_associated';
    }

    /**
     * @return array
     */
    private function buildAliasChoices()
    {
        $choices = [];

        foreach ($this->associationConfig->getAssociations() as $association) {
            $choices[$association->getTitle()] = $association->getAlias();
        }

        return $choices;
    }
}
