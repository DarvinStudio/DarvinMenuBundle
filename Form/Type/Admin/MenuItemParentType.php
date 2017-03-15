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
use Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader;
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
     * @var \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader
     */
    private $slugMapItemCustomObjectLoader;

    /**
     * @param \Darvin\Utils\Locale\LocaleProviderInterface             $localeProvider                Locale provider
     * @param \Darvin\MenuBundle\Admin\Sorter\MenuItemSorter           $menuItemSorter                Menu item sorter
     * @param \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader $slugMapItemCustomObjectLoader Slug map item custom object loader
     */
    public function __construct(
        LocaleProviderInterface $localeProvider,
        MenuItemSorter $menuItemSorter,
        SlugMapItemCustomObjectLoader $slugMapItemCustomObjectLoader
    ) {
        $this->localeProvider = $localeProvider;
        $this->menuItemSorter = $menuItemSorter;
        $this->slugMapItemCustomObjectLoader = $slugMapItemCustomObjectLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $menuItems = $slugMapItems = [];

        /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
        foreach ($view->vars['choices'] as $choice) {
            /** @var \Darvin\MenuBundle\Entity\Menu\Item $menuItem */
            $menuItem = $choice->data;
            $menuItems[] = $menuItem;

            if (null !== $menuItem->getSlugMapItem()) {
                $slugMapItems[] = $menuItem->getSlugMapItem();
            }

            $choice->attr = array_merge($choice->attr, [
                'class'        => 'slave_input',
                'data-master'  => '.menu',
                'data-show-on' => $menuItem->getMenu(),
                'disabled'     => $menuItem === $form->getParent()->getData(),
            ]);
        }

        $this->slugMapItemCustomObjectLoader->loadCustomObjects($slugMapItems);

        $choices = [];

        foreach ($this->menuItemSorter->sort($menuItems) as $menuItem) {
            $choice = $view->vars['choices'][$menuItem->getId()];
            $choice->label = $menuItem->__toString();
            $choices[$menuItem->getId()] = $choice;
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
            'required'      => false,
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
