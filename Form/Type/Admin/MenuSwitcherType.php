<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Darvin\MenuBundle\Configuration\MenuConfiguration;
use Darvin\MenuBundle\Switcher\MenuSwitcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Menu switcher admin form type
 */
class MenuSwitcherType extends AbstractType
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
        $menuConfig   = $this->menuConfig;
        $menuSwitcher = $this->menuSwitcher;

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($menuConfig, $menuSwitcher) {
                $entity = $event->getForm()->getParent()->getData();

                $menuAliases = [];

                foreach ($menuConfig->getMenus() as $menu) {
                    if ($menuSwitcher->isEnabled($menu->getAlias(), $entity)) {
                        $menuAliases[] = $menu->getAlias();
                    }
                }

                $event->setData($menuAliases);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($menuConfig, $menuSwitcher) {
                $entity = $event->getForm()->getParent()->getData();

                $menuAliases = $event->getData();

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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'    => 'menu_switcher.title',
            'mapped'   => false,
            'choices'  => $this->buildChoices(),
            'multiple' => true,
            'expanded' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * @return array
     */
    private function buildChoices()
    {
        $choices = [];

        foreach ($this->menuConfig->getMenus() as $menu) {
            $choices[$menu->getTitle()] = $menu->getAlias();
        }

        return $choices;
    }
}
