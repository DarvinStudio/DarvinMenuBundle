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

use Darvin\MenuBundle\Admin\Sorter\MenuItemSorter;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Repository\Menu\ItemRepository;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Menu item parent admin form type
 */
class MenuItemParentType extends AbstractType
{
    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var \Darvin\MenuBundle\Admin\Sorter\MenuItemSorter
     */
    private $menuItemSorter;

    /**
     * @param \Darvin\Utils\Locale\LocaleProviderInterface   $localeProvider Locale provider
     * @param \Darvin\MenuBundle\Admin\Sorter\MenuItemSorter $menuItemSorter Menu item sorter
     */
    public function __construct(LocaleProviderInterface $localeProvider, MenuItemSorter $menuItemSorter)
    {
        $this->localeProvider = $localeProvider;
        $this->menuItemSorter = $menuItemSorter;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $menuItems = [];

        /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
        foreach ($view->vars['choices'] as $choice) {
            $menuItems[] = $choice->data;
        }

        $choices = [];

        /** @var \Darvin\MenuBundle\Entity\Menu\Item $menuItem */
        foreach ($this->menuItemSorter->sort($menuItems) as $menuItem) {
            $choices[$menuItem->getId()] = $view->vars['choices'][$menuItem->getId()];
        }

        $view->vars['choices'] = $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $locale = $this->localeProvider->getCurrentLocale();

        $resolver->setDefaults([
            'class'         => Item::class,
            'query_builder' => function (ItemRepository $repository) use ($locale) {
                return $repository->getAdminBuilder($locale);
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
