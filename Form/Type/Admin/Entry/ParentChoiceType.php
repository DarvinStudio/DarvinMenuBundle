<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin\Entry;

use Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface;
use Darvin\MenuBundle\Admin\Sorter\MenuEntrySorterInterface;
use Darvin\MenuBundle\Entity\MenuEntryInterface;
use Darvin\MenuBundle\Repository\MenuEntryRepository;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Menu entry parent choice admin form type
 */
class ParentChoiceType extends AbstractType
{
    /**
     * @var \Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface
     */
    private $contentReferenceObjectLoader;

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var \Darvin\MenuBundle\Admin\Sorter\MenuEntrySorterInterface
     */
    private $menuEntrySorter;

    /**
     * @param \Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface $contentReferenceObjectLoader Content reference object loader
     * @param \Darvin\Utils\ORM\EntityResolverInterface                             $entityResolver               Entity resolver
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                          $localeProvider               Locale provider
     * @param \Darvin\MenuBundle\Admin\Sorter\MenuEntrySorterInterface              $menuEntrySorter              Menu entry sorter
     */
    public function __construct(
        ContentReferenceObjectLoaderInterface $contentReferenceObjectLoader,
        EntityResolverInterface $entityResolver,
        LocaleProviderInterface $localeProvider,
        MenuEntrySorterInterface $menuEntrySorter
    ) {
        $this->contentReferenceObjectLoader = $contentReferenceObjectLoader;
        $this->entityResolver = $entityResolver;
        $this->localeProvider = $localeProvider;
        $this->menuEntrySorter = $menuEntrySorter;
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $entries = $contentReferences = [];

        /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
        foreach ($view->vars['choices'] as $choice) {
            /** @var \Darvin\MenuBundle\Entity\MenuEntry $entry */
            $entry = $choice->data;
            $entries[] = $entry;

            if (null !== $entry->getContentReference()) {
                $contentReferences[] = $entry->getContentReference();
            }

            $choice->attr = array_merge($choice->attr, [
                'data-master'  => '.menu',
                'data-show-on' => $entry->getMenu(),
                'disabled'     => $entry === $form->getParent()->getData(),
            ]);
        }

        $this->contentReferenceObjectLoader->loadObjects($contentReferences);

        $choices = [];

        foreach ($this->menuEntrySorter->sort($entries) as $entry) {
            $choice = $view->vars['choices'][$entry->getId()];
            $choice->label = $entry->__toString();
            $choices[$entry->getId()] = $choice;
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
            'class'         => $this->entityResolver->resolve(MenuEntryInterface::class),
            'required'      => false,
            'query_builder' => function (MenuEntryRepository $repository) use ($locale): QueryBuilder {
                return $repository->createBuilderForAdminForm(null, $locale);
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
        return 'darvin_menu_admin_entry_parent_choice';
    }
}
