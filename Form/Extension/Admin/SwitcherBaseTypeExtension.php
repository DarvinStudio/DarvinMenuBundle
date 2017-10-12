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
use Darvin\MenuBundle\Form\Type\Admin\MenuSwitcherType;
use Darvin\MenuBundle\Switcher\MenuSwitcher;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Menu switcher base admin form type extension
 */
class SwitcherBaseTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private $menuConfig;

    /**
     * @var \Darvin\MenuBundle\Switcher\MenuSwitcher
     */
    private $menuSwitcher;

    /**
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration $menuConfig   Menu configuration
     * @param \Darvin\MenuBundle\Switcher\MenuSwitcher           $menuSwitcher Menu switcher
     */
    public function __construct(MenuConfiguration $menuConfig, MenuSwitcher $menuSwitcher)
    {
        $this->menuConfig = $menuConfig;
        $this->menuSwitcher = $menuSwitcher;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldName = null;

        /** @var \Symfony\Component\Form\FormBuilderInterface $field */
        foreach ($builder->all() as $name => $field) {
            if ($field->getType()->getInnerType() instanceof MenuSwitcherType) {
                $fieldName = $name;

                break;
            }
        }
        if (empty($fieldName)) {
            return;
        }

        $menuConfig   = $this->menuConfig;
        $menuSwitcher = $this->menuSwitcher;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($fieldName, $menuConfig, $menuSwitcher) {
            $entity      = $event->getData();
            $menuAliases = $event->getForm()->get($fieldName)->getData();

            foreach ($menuConfig->getMenus() as $menu) {
                if (in_array($menu->getAlias(), $menuAliases)) {
                    $menuSwitcher->enable($menu->getAlias(), $entity);
                } else {
                    $menuSwitcher->disable($menu->getAlias(), $entity);
                }
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
