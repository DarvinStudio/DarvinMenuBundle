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
use Darvin\MenuBundle\Form\Type\Admin\MenuItemManagerType;
use Darvin\MenuBundle\Item\MenuItemManager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Menu item manager base admin form type extension
 */
class ItemManagerBaseTypeExtension extends AbstractTypeExtension
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
        $fieldName = null;

        /** @var \Symfony\Component\Form\FormBuilderInterface $field */
        foreach ($builder->all() as $name => $field) {
            if ($field->getType()->getInnerType() instanceof MenuItemManagerType) {
                $fieldName = $name;

                break;
            }
        }
        if (empty($fieldName)) {
            return;
        }

        $menuConfig      = $this->menuConfig;
        $menuItemManager = $this->menuItemManager;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($fieldName, $menuConfig, $menuItemManager) {
            $entity      = $event->getData();
            $menuAliases = $event->getForm()->get($fieldName)->getData();

            foreach ($menuConfig->getMenus() as $menu) {
                if (in_array($menu->getAlias(), $menuAliases)) {
                    $menuItemManager->scheduleForAdding($menu->getAlias(), $entity);
                } else {
                    $menuItemManager->scheduleForRemoval($menu->getAlias(), $entity);
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
