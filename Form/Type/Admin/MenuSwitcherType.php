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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
                $parentForm = $event->getForm()->getParent();

                $menus = $menuConfig->getMenus();

                if (empty($menus)) {
                    $parentForm->remove($event->getForm()->getName());

                    return;
                }

                $entity = $parentForm->getData();

                $defaultMenuAliases = 'new' === $parentForm->getConfig()->getOption('action_type')
                    ? $menuSwitcher->getDefaultMenus($entity)
                    : [];

                foreach ($menus as $menu) {
                    $attr = [];

                    if (in_array($menu->getAlias(), $defaultMenuAliases)) {
                        $attr['data-default'] = 1;
                    }

                    $event->getForm()->add($menu->getAlias(), CheckboxType::class, [
                        'label' => $menu->getTitle(),
                        'attr'  => $attr,
                    ]);
                }

                $data = [];

                foreach ($menus as $menu) {
                    if ($menuSwitcher->isMenuEnabled($entity, $menu->getAlias())
                        || in_array($menu->getAlias(), $defaultMenuAliases)
                    ) {
                        $data[$menu->getAlias()] = true;
                    }
                }

                $event->setData($data);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($menuConfig, $menuSwitcher) {
                $data   = $event->getData();
                $entity = $event->getForm()->getParent()->getData();

                foreach ($menuConfig->getMenus() as $menu) {
                    $menuSwitcher->toggleMenu($entity, $menu->getAlias(), $data[$menu->getAlias()]);
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['parent_selector'] = $options['parent_selector'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'           => 'menu_switcher.title',
                'mapped'          => false,
                'required'        => false,
                'parent_selector' => '.parent',
            ])
            ->setAllowedTypes('parent_selector', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_menu_switcher';
    }
}
