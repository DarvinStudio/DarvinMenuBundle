<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface;
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
     * @var \Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface
     */
    private $menuProvider;

    /**
     * @var \Darvin\MenuBundle\Switcher\MenuSwitcherInterface
     */
    private $menuSwitcher;

    /**
     * @param \Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface $menuProvider Menu provider
     * @param \Darvin\MenuBundle\Switcher\MenuSwitcherInterface                  $menuSwitcher Menu switcher
     */
    public function __construct(MenuProviderRegistryInterface $menuProvider, MenuSwitcherInterface $menuSwitcher)
    {
        $this->menuProvider = $menuProvider;
        $this->menuSwitcher = $menuSwitcher;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $menuProvider = $this->menuProvider;
        $menuSwitcher = $this->menuSwitcher;

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($menuProvider, $menuSwitcher): void {
                $parentForm = $event->getForm()->getParent();

                $menuCollection = $menuProvider->getMenuCollection();

                if (empty($menuCollection)) {
                    $parentForm->remove($event->getForm()->getName());

                    return;
                }

                $entity = $parentForm->getData();

                $defaultMenuNames = 'new' === $parentForm->getConfig()->getOption('action_type')
                    ? $menuSwitcher->getDefaultMenus($entity)
                    : [];

                foreach ($menuCollection as $menu) {
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

                foreach ($menuCollection as $menu) {
                    if ($menuSwitcher->isMenuEnabled($entity, $menu->getName())
                        || in_array($menu->getName(), $defaultMenuNames)
                    ) {
                        $data[$menu->getName()] = true;
                    }
                }

                $event->setData($data);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($menuProvider, $menuSwitcher): void {
                $data   = $event->getData();
                $entity = $event->getForm()->getParent()->getData();

                foreach ($menuProvider->getMenuCollection() as $menu) {
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
