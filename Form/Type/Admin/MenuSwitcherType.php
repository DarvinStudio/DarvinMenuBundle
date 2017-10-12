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
use Darvin\MenuBundle\Item\MenuItemManager;
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
     * @var \Darvin\MenuBundle\Item\MenuItemManager
     */
    private $menuItemManager;

    /**
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration $menuConfig      Menu configuration
     * @param \Darvin\MenuBundle\Item\MenuItemManager            $menuItemManager Menu item manager
     */
    public function __construct(MenuConfiguration $menuConfig, MenuItemManager $menuItemManager)
    {
        $this->menuConfig = $menuConfig;
        $this->menuItemManager = $menuItemManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $menuConfig      = $this->menuConfig;
        $menuItemManager = $this->menuItemManager;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($menuConfig, $menuItemManager) {
            $entity = $event->getForm()->getParent()->getData();

            $data = [];

            foreach ($menuConfig->getMenus() as $menu) {
                if ($menuItemManager->exists($menu->getAlias(), $entity)) {
                    $data[] = $menu->getAlias();
                }
            }

            $event->setData($data);
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
