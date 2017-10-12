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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Menu item manager admin form type
 */
class MenuItemManagerType extends AbstractType
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'    => 'menu_item.manager.form.label',
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
