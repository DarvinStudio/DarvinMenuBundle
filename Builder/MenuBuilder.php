<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Builder;

use Darvin\ContentBundle\Disableable\DisableableInterface;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Hideable\HideableInterface;
use Darvin\ContentBundle\Repository\SlugMapItemRepository;
use Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface;
use Darvin\MenuBundle\Entity\MenuEntry;
use Darvin\MenuBundle\Item\Factory\Registry\ItemFactoryRegistryInterface;
use Darvin\MenuBundle\Repository\MenuEntryRepository;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sortable\SortableListener;
use Knp\Menu\ItemInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Menu builder
 */
class MenuBuilder implements MenuBuilderInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Darvin\MenuBundle\Item\Factory\Registry\ItemFactoryRegistryInterface
     */
    private $itemFactoryRegistry;

    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface
     */
    private $slugMapObjectLoader;

    /**
     * @var \Gedmo\Sortable\SortableListener
     */
    private $sortableListener;

    /**
     * @var array
     */
    private $entityConfig;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver|null
     */
    private $optionsResolver;

    /**
     * @var array
     */
    private $slugPartSeparators;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface                                  $em                  Entity manager
     * @param \Darvin\Utils\ORM\EntityResolverInterface                             $entityResolver      Entity resolver
     * @param \Darvin\MenuBundle\Item\Factory\Registry\ItemFactoryRegistryInterface $itemFactoryRegistry Item factory registry
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                          $localeProvider      Locale provider
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                        $metadataFactory     Extended metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface           $propertyAccessor    Property accessor
     * @param \Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface               $slugMapObjectLoader Slug map object loader
     * @param \Gedmo\Sortable\SortableListener                                      $sortableListener    Sortable event listener
     * @param array                                                                 $entityConfig        Entity configuration
     */
    public function __construct(
        EntityManagerInterface $em,
        EntityResolverInterface $entityResolver,
        ItemFactoryRegistryInterface $itemFactoryRegistry,
        LocaleProviderInterface $localeProvider,
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor,
        SlugMapObjectLoaderInterface $slugMapObjectLoader,
        SortableListener $sortableListener,
        array $entityConfig
    ) {
        $this->em = $em;
        $this->entityResolver = $entityResolver;
        $this->itemFactoryRegistry = $itemFactoryRegistry;
        $this->localeProvider = $localeProvider;
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->slugMapObjectLoader = $slugMapObjectLoader;
        $this->sortableListener = $sortableListener;
        $this->entityConfig = $entityConfig;

        $this->optionsResolver    = null;
        $this->slugPartSeparators = [];
    }

    /**
     * {@inheritDoc}
     */
    public function buildMenu(array $options = []): ItemInterface
    {
        $options = $this->getOptionsResolver()->resolve($options);

        $menuName = $options['menu'];

        $root = $this->itemFactoryRegistry->createItem($menuName);

        $entries = $this->getEntries($menuName, $options);

        $this->addItems($root, $entries, $options);

        return $root;
    }

    /**
     * @param \Knp\Menu\ItemInterface               $root    Root item
     * @param \Darvin\MenuBundle\Entity\MenuEntry[] $entries Menu entries
     * @param array                                 $options Options
     */
    private function addItems(ItemInterface $root, array $entries, array $options): void
    {
        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = $parentSlugs = $separatorCounts = [];

        foreach ($entries as $key => $entry) {
            $slugMapItem = $entry->getSlugMapItem();

            if (null !== $slugMapItem) {
                $separator = $this->getSlugPartsSeparator($slugMapItem->getObjectClass(), $slugMapItem->getProperty());

                if (false === $separator) {
                    unset($entries[$key]);

                    continue;
                }
                if ($entry->isShowChildren() && (null === $options['depth'] || $entry->getLevel() < $options['depth'])) {
                    $parentSlug = $slugMapItem->getSlug().$separator;
                    $parentSlugs[$entry->getId()] = $parentSlug;

                    $separatorCounts[$entry->getId()] = substr_count($parentSlug, $separator);
                }
            }

            $item = $this->itemFactoryRegistry->createItem($entry);
            $items[$entry->getId()] = $item;

            if (null === $entry->getParent()) {
                $root->addChild($item);

                continue;
            }

            $parentId = $entry->getParent()->getId();

            if (isset($items[$parentId])) {
                $items[$parentId]->addChild($item);
            }
        }
        if (empty($parentSlugs)) {
            return;
        }

        $classBlacklist = [];

        foreach ($this->entityConfig as $class => $config) {
            if (!$config['slug_children']) {
                $classBlacklist = array_merge($classBlacklist, [$class, $this->entityResolver->resolve($class)]);
            }
        }
        foreach ($this->getSlugMapItemRepository()->getChildrenBySlugs(array_unique($parentSlugs), $classBlacklist) as $parentSlug => $childSlugMapItems) {
            foreach (array_keys($parentSlugs, $parentSlug) as $entityId) {
                $this->addChildren($items[$entityId], $separatorCounts[$entityId], $childSlugMapItems, $options);
            }
        }
    }

    /**
     * @param \Knp\Menu\ItemInterface                    $parent            Parent item
     * @param int                                        $separatorCount    Count of separators in the parent item's slug
     * @param \Darvin\ContentBundle\Entity\SlugMapItem[] $childSlugMapItems Child slug map items
     * @param array                                      $options           Options
     */
    private function addChildren(ItemInterface $parent, int $separatorCount, array $childSlugMapItems, array $options): void
    {
        $childSlugMapItems = $this->prepareChildSlugMapItems(
            $childSlugMapItems,
            null !== $options['depth'] ? $separatorCount + $options['depth'] - $parent->getLevel() : null
        );

        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = [];

        foreach ($childSlugMapItems as $id => $slugMapItem) {
            $item = $this->itemFactoryRegistry->createItem($slugMapItem['object']);
            $items[$id] = $item;

            $parentId = $slugMapItem['parent_id'];

            if (null === $parentId) {
                if (1 === $slugMapItem['separator_count'] - $separatorCount) {
                    $parent->addChild($item);
                }

                continue;
            }
            if (isset($items[$parentId])) {
                $items[$parentId]->addChild($item);
            }
        }
    }

    /**
     * @param string $menuName Menu name
     * @param array  $options  Options
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntry[]
     */
    private function getEntries(string $menuName, array $options): array
    {
        $entries = $this->getMenuEntryRepository()->getForMenuBuilder($menuName, $options['depth'], $this->localeProvider->getCurrentLocale());

        if (empty($entries)) {
            return $entries;
        }

        $slugMapItems = [];

        foreach ($entries as $entry) {
            if (null !== $entry->getSlugMapItem()) {
                $slugMapItems[$entry->getId()] = $entry->getSlugMapItem();
            }
        }

        $this->slugMapObjectLoader->loadObjects($slugMapItems);

        foreach ($entries as $key => $entry) {
            if (null !== $entry->getSlugMapItem() && !$this->isSlugMapItemActive($entry->getSlugMapItem())) {
                unset($entries[$key]);
            }
        }

        return $entries;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem[] $childSlugMapItems Child slug map items
     * @param int|null                                   $maxSeparatorCount Maximum separator count
     *
     * @return array
     */
    private function prepareChildSlugMapItems(array $childSlugMapItems, ?int $maxSeparatorCount): array
    {
        $children = [];

        if (empty($childSlugMapItems)) {
            return $children;
        }

        $separator = $this->getSlugPartsSeparator($childSlugMapItems[0]->getObjectClass(), $childSlugMapItems[0]->getProperty());

        if (false === $separator) {
            return $children;
        }

        $separatorCounts = [];

        foreach ($childSlugMapItems as $key => $slugMapItem) {
            $separatorCount = substr_count($slugMapItem->getSlug(), $separator) + 1;

            if (null !== $maxSeparatorCount && $separatorCount > $maxSeparatorCount) {
                unset($childSlugMapItems[$key]);

                continue;
            }

            $separatorCounts[$slugMapItem->getId()] = $separatorCount;
        }

        $this->slugMapObjectLoader->loadObjects($childSlugMapItems);

        foreach ($childSlugMapItems as $key => $slugMapItem) {
            if (!$this->isSlugMapItemActive($slugMapItem)) {
                unset($childSlugMapItems[$key]);

                continue;
            }

            $children[$slugMapItem->getId()] = [
                'object'          => $slugMapItem,
                'slug'            => $slugMapItem->getSlug(),
                'separator_count' => $separatorCounts[$slugMapItem->getId()],
                'parent_id'       => null,
            ];
        }
        foreach ($children as $childId => $child) {
            foreach ($children as $otherChildId => $otherChild) {
                if (1 === $child['separator_count'] - $otherChild['separator_count'] && 0 === strpos($child['slug'], $otherChild['slug'].$separator)) {
                    $children[$childId]['parent_id'] = $otherChildId;
                }
            }
        }

        $em               = $this->em;
        $entityResolver   = $this->entityResolver;
        $propertyAccessor = $this->propertyAccessor;
        $sortableListener = $this->sortableListener;

        uasort($children, function (array $a, array $b) use ($em, $entityResolver, $propertyAccessor, $sortableListener) {
            if ($a['separator_count'] !== $b['separator_count']) {
                return $a['separator_count'] > $b['separator_count'] ? 1 : -1;
            }

            /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItemA */
            $slugMapItemA = $a['object'];
            /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItemB */
            $slugMapItemB = $b['object'];

            $classA = $entityResolver->resolve($slugMapItemA->getObjectClass());
            $classB = $entityResolver->resolve($slugMapItemB->getObjectClass());

            if ($classA !== $classB) {
                return $classA > $classB ? 1 : -1;
            }

            $sortableConfig = $sortableListener->getConfiguration($em, $classA);

            if (empty($sortableConfig)) {
                return 0;
            }

            $positionA = $propertyAccessor->getValue($slugMapItemA->getObject(), $sortableConfig['position']);
            $positionB = $propertyAccessor->getValue($slugMapItemB->getObject(), $sortableConfig['position']);

            return $positionA <=> $positionB;
        });

        return $children;
    }

    /**
     * @param string $class    Class
     * @param string $property Property
     *
     * @return bool|string
     */
    private function getSlugPartsSeparator(string $class, string $property)
    {
        if (!isset($this->slugPartSeparators[$class][$property])) {
            if (!isset($this->slugPartSeparators[$class])) {
                $this->slugPartSeparators[$class] = [];
            }

            $meta = $this->metadataFactory->getExtendedMetadata($class)['slugs'];
            $this->slugPartSeparators[$class][$property] = isset($meta[$property]) ? $meta[$property]['separator'] : false;
        }

        return $this->slugPartSeparators[$class][$property];
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return bool
     */
    private function isSlugMapItemActive(SlugMapItem $slugMapItem): bool
    {
        $customObject = $slugMapItem->getObject();

        if (null === $customObject) {
            return false;
        }
        if ($customObject instanceof DisableableInterface && !$customObject->isEnabled()) {
            return false;
        }
        if ($customObject instanceof HideableInterface && $customObject->isHidden()) {
            return false;
        }

        return true;
    }

    /**
     * @return \Symfony\Component\OptionsResolver\OptionsResolver
     */
    private function getOptionsResolver(): OptionsResolver
    {
        if (null === $this->optionsResolver) {
            $resolver = new OptionsResolver();

            $this->configureOptions($resolver);

            $this->optionsResolver = $resolver;
        }

        return $this->optionsResolver;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Options resolver
     */
    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('menu')
            ->setDefault('depth', null)
            ->setAllowedTypes('menu', 'string')
            ->setAllowedTypes('depth', ['integer', 'null', 'string'])
            ->setNormalizer('depth', function (Options $options, $depth): ?int {
                if (null !== $depth) {
                    $depth = (int)$depth;
                }

                return $depth;
            });
    }

    /**
     * @return \Darvin\MenuBundle\Repository\MenuEntryRepository
     */
    private function getMenuEntryRepository(): MenuEntryRepository
    {
        return $this->em->getRepository(MenuEntry::class);
    }

    /**
     * @return \Darvin\ContentBundle\Repository\SlugMapItemRepository
     */
    private function getSlugMapItemRepository(): SlugMapItemRepository
    {
        return $this->em->getRepository(SlugMapItem::class);
    }
}
