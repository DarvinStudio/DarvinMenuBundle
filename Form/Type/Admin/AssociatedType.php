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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Admin associated form type
 */
class AssociatedType extends AbstractType
{
    /**
     * @var \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private $associationConfig;

    /**
     * @param \Darvin\MenuBundle\Configuration\AssociationConfiguration $associationConfig Association configuration
     */
    public function __construct(AssociationConfiguration $associationConfig)
    {
        $this->associationConfig = $associationConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('alias', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'choices'           => $this->buildAliasChoices(),
            'choices_as_values' => true,
            'constraints'       => new NotBlank(),
            'mapped'            => false,
        ]);

        foreach ($this->associationConfig->getAssociations() as $association) {
            $builder->add('associated_'.$association->getAlias(), 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'label'  => $association->getTitle(),
                'class'  => $association->getClass(),
                'mapped' => false,
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
