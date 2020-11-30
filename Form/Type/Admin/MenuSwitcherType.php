<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Darvin\MenuBundle\Configuration\MenuConfigurationInterface;
use Darvin\MenuBundle\Switcher\MenuSwitcherInterface;
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
     * @var \Darvin\MenuBundle\Configuration\MenuConfigurationInterface
     */
    private $menuConfig;

    /**
     * @var \Darvin\MenuBundle\Switcher\MenuSwitcherInterface
     */
    private $menuSwitcher;

    /**
     * @param \Darvin\MenuBundle\Configuration\MenuConfigurationInterface $menuConfig   Menu configuration
     * @param \Darvin\MenuBundle\Switcher\MenuSwitcherInterface           $menuSwitcher Menu switcher
     */
    public function __construct(MenuConfigurationInterface $menuConfig, MenuSwitcherInterface $menuSwitcher)
    {
        $this->menuConfig = $menuConfig;
        $this->menuSwitcher = $menuSwitcher;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

                $defaultMenuNames = 'new' === $parentForm->getConfig()->getOption('action_type')
                    ? $menuSwitcher->getDefaultMenus($entity)
                    : [];

                foreach ($menus as $menu) {
                    $attr = [];

                    if (in_array($menu->getName(), $defaultMenuNames)) {
                        $attr['data-default'] = 1;
                    }

                    $event->getForm()->add($menu->getName(), CheckboxType::class, [
                        'label' => $menu->getTitle(),
                        'attr'  => $attr,
                    ]);
                }

                $data = [];

                foreach ($menus as $menu) {
                    if ($menuSwitcher->isMenuEnabled($entity, $menu->getName())
                        || in_array($menu->getName(), $defaultMenuNames)
                    ) {
                        $data[$menu->getName()] = true;
                    }
                }

                $event->setData($data);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($menuConfig, $menuSwitcher) {
                $data   = $event->getData();
                $entity = $event->getForm()->getParent()->getData();

                foreach ($menuConfig->getMenus() as $menu) {
                    $menuSwitcher->toggleMenu($entity, $menu->getName(), $data[$menu->getName()]);
                }
            });
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['parent_selector'] = $options['parent_selector'];
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
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
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_menu_switcher';
    }
}
