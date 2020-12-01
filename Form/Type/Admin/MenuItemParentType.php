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

use Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface;
use Darvin\MenuBundle\Admin\Sorter\MenuItemSorter;
use Darvin\MenuBundle\Entity\MenuItem;
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
     * @var \Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface
     */
    private $slugMapObjectLoader;

    /**
     * @param \Darvin\Utils\Locale\LocaleProviderInterface            $localeProvider      Locale provider
     * @param \Darvin\MenuBundle\Admin\Sorter\MenuItemSorter          $menuItemSorter      Menu item sorter
     * @param \Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface $slugMapObjectLoader Slug map object loader
     */
    public function __construct(
        LocaleProviderInterface $localeProvider,
        MenuItemSorter $menuItemSorter,
        SlugMapObjectLoaderInterface $slugMapObjectLoader
    ) {
        $this->localeProvider = $localeProvider;
        $this->menuItemSorter = $menuItemSorter;
        $this->slugMapObjectLoader = $slugMapObjectLoader;
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $menuItems = $slugMapItems = [];

        /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
        foreach ($view->vars['choices'] as $choice) {
            /** @var \Darvin\MenuBundle\Entity\MenuItem $menuItem */
            $menuItem = $choice->data;
            $menuItems[] = $menuItem;

            if (null !== $menuItem->getSlugMapItem()) {
                $slugMapItems[] = $menuItem->getSlugMapItem();
            }

            $choice->attr = array_merge($choice->attr, [
                'data-master'  => '.menu',
                'data-show-on' => $menuItem->getMenu(),
                'disabled'     => $menuItem === $form->getParent()->getData(),
            ]);
        }

        $this->slugMapObjectLoader->loadObjects($slugMapItems);

        $choices = [];

        foreach ($this->menuItemSorter->sort($menuItems) as $menuItem) {
            $choice = $view->vars['choices'][$menuItem->getId()];
            $choice->label = $menuItem->__toString();
            $choices[$menuItem->getId()] = $choice;
        }

        $view->vars['choices'] = $choices;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $locale = $this->localeProvider->getCurrentLocale();

        $resolver->setDefaults([
            'class'         => MenuItem::class,
            'required'      => false,
            'query_builder' => function (ItemRepository $repository) use ($locale) {
                return $repository->getAdminBuilder(null, $locale);
            },
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): string
    {
        return EntityType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_menu_admin_menu_item_parent';
    }
}
