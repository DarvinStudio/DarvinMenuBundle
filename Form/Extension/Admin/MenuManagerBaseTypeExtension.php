<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Extension\Admin;

use Darvin\AdminBundle\Form\Type\BaseType;
use Darvin\MenuBundle\Configuration\MenuConfiguration;
use Darvin\MenuBundle\Form\Type\Admin\MenuManagerType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Menu manager base admin form type extension
 */
class MenuManagerBaseTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private $menuConfig;

    /**
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration $menuConfig Menu configuration
     */
    public function __construct(MenuConfiguration $menuConfig)
    {
        $this->menuConfig = $menuConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldName = null;

        /** @var \Symfony\Component\Form\FormBuilderInterface $field */
        foreach ($builder->all() as $name => $field) {
            if ($field->getType()->getInnerType() instanceof MenuManagerType) {
                $fieldName = $name;

                break;
            }
        }
        if (empty($fieldName)) {
            return;
        }

        $menuConfig = $this->menuConfig;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($fieldName, $menuConfig) {
            $entity      = $event->getData();
            $menuAliases = $event->getForm()->get($fieldName)->getData();

            $toInsert = $toDelete = [];

            foreach ($menuConfig->getMenus() as $menu) {
                in_array($menu->getAlias(), $menuAliases)
                    ? $toInsert[] = $menu->getAlias()
                    : $toDelete[] = $menu->getAlias();
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return BaseType::class;
    }
}
