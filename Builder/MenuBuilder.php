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
use Darvin\ContentBundle\Entity\ContentReference;
use Darvin\ContentBundle\Hideable\HideableInterface;
use Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface;
use Darvin\ContentBundle\Repository\ContentReferenceRepository;
use Darvin\MenuBundle\Entity\MenuEntryInterface;
use Darvin\MenuBundle\Knp\Item\Factory\Registry\KnpItemFactoryRegistryInterface;
use Darvin\MenuBundle\Provider\Model\Menu;
use Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface;
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
     * @var \Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface
     */
    private $contentReferenceObjectLoader;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Darvin\MenuBundle\Knp\Item\Factory\Registry\KnpItemFactoryRegistryInterface
     */
    private $knpItemFactoryRegistry;

    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var \Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface
     */
    private $menuProvider;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

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
     * @param \Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface        $contentReferenceObjectLoader Content reference object loader
     * @param \Doctrine\ORM\EntityManagerInterface                                         $em                           Entity manager
     * @param \Darvin\Utils\ORM\EntityResolverInterface                                    $entityResolver               Entity resolver
     * @param \Darvin\MenuBundle\Knp\Item\Factory\Registry\KnpItemFactoryRegistryInterface $knpItemFactoryRegistry       KNP menu item factory registry
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                                 $localeProvider               Locale provider
     * @param \Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface           $menuProvider                 Menu provider
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                               $metadataFactory              Extended metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface                  $propertyAccessor             Property accessor
     * @param \Gedmo\Sortable\SortableListener                                             $sortableListener             Sortable event listener
     * @param array                                                                        $entityConfig                 Entity configuration
     */
    public function __construct(
        ContentReferenceObjectLoaderInterface $contentReferenceObjectLoader,
        EntityManagerInterface $em,
        EntityResolverInterface $entityResolver,
        KnpItemFactoryRegistryInterface $knpItemFactoryRegistry,
        LocaleProviderInterface $localeProvider,
        MenuProviderRegistryInterface $menuProvider,
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor,
        SortableListener $sortableListener,
        array $entityConfig
    ) {
        $this->contentReferenceObjectLoader = $contentReferenceObjectLoader;
        $this->em = $em;
        $this->entityResolver = $entityResolver;
        $this->knpItemFactoryRegistry = $knpItemFactoryRegistry;
        $this->localeProvider = $localeProvider;
        $this->menuProvider = $menuProvider;
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
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

        $root = $this->knpItemFactoryRegistry->createItem($menuName);

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
            $contentReference = $entry->getContentReference();

            if (null !== $contentReference) {
                $separator = $this->getSlugPartsSeparator($contentReference->getObjectClass(), $contentReference->getProperty());

                if (false === $separator) {
                    unset($entries[$key]);

                    continue;
                }
                if ($entry->isShowChildren() && (null === $options['depth'] || $entry->getLevel() < $options['depth'])) {
                    $parentSlug = $contentReference->getSlug().$separator;
                    $parentSlugs[$entry->getId()] = $parentSlug;

                    $separatorCounts[$entry->getId()] = substr_count($parentSlug, $separator);
                }
            }

            $item = $this->knpItemFactoryRegistry->createItem($entry);
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
        foreach ($this->getContentReferenceRepository()->getChildrenBySlugs(array_unique($parentSlugs), $classBlacklist) as $parentSlug => $childContentReferences) {
            foreach (array_keys($parentSlugs, $parentSlug) as $entityId) {
                $this->addChildren($items[$entityId], $separatorCounts[$entityId], $childContentReferences, $options);
            }
        }
    }

    /**
     * @param \Knp\Menu\ItemInterface                         $parent                 Parent item
     * @param int                                             $separatorCount         Count of separators in the parent item's slug
     * @param \Darvin\ContentBundle\Entity\ContentReference[] $childContentReferences Child content references
     * @param array                                           $options                Options
     */
    private function addChildren(ItemInterface $parent, int $separatorCount, array $childContentReferences, array $options): void
    {
        $childContentReferences = $this->prepareChildContentReferences(
            $childContentReferences,
            null !== $options['depth'] ? $separatorCount + $options['depth'] - $parent->getLevel() : null
        );

        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = [];

        foreach ($childContentReferences as $id => $contentReference) {
            $item = $this->knpItemFactoryRegistry->createItem($contentReference['object']);
            $items[$id] = $item;

            $parentId = $contentReference['parent_id'];

            if (null === $parentId) {
                if (1 === $contentReference['separator_count'] - $separatorCount) {
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

        $contentReferences = [];

        foreach ($entries as $entry) {
            if (null !== $entry->getContentReference()) {
                $contentReferences[$entry->getId()] = $entry->getContentReference();
            }
        }

        $this->contentReferenceObjectLoader->loadObjects($contentReferences);

        foreach ($entries as $key => $entry) {
            if (null !== $entry->getContentReference() && !$this->isContentReferenceActive($entry->getContentReference())) {
                unset($entries[$key]);
            }
        }

        return $entries;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\ContentReference[] $childContentReferences Child content references
     * @param int|null                                        $maxSeparatorCount      Maximum separator count
     *
     * @return array
     */
    private function prepareChildContentReferences(array $childContentReferences, ?int $maxSeparatorCount): array
    {
        $children = [];

        if (empty($childContentReferences)) {
            return $children;
        }

        $separator = $this->getSlugPartsSeparator($childContentReferences[0]->getObjectClass(), $childContentReferences[0]->getProperty());

        if (false === $separator) {
            return $children;
        }

        $separatorCounts = [];

        foreach ($childContentReferences as $key => $contentReference) {
            $separatorCount = substr_count($contentReference->getSlug(), $separator) + 1;

            if (null !== $maxSeparatorCount && $separatorCount > $maxSeparatorCount) {
                unset($childContentReferences[$key]);

                continue;
            }

            $separatorCounts[$contentReference->getId()] = $separatorCount;
        }

        $this->contentReferenceObjectLoader->loadObjects($childContentReferences);

        foreach ($childContentReferences as $key => $contentReference) {
            if (!$this->isContentReferenceActive($contentReference)) {
                unset($childContentReferences[$key]);

                continue;
            }

            $children[$contentReference->getId()] = [
                'object'          => $contentReference,
                'slug'            => $contentReference->getSlug(),
                'separator_count' => $separatorCounts[$contentReference->getId()],
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

        uasort($children, function (array $a, array $b) use ($em, $entityResolver, $propertyAccessor, $sortableListener): int {
            if ($a['separator_count'] !== $b['separator_count']) {
                return $a['separator_count'] > $b['separator_count'] ? 1 : -1;
            }

            /** @var \Darvin\ContentBundle\Entity\ContentReference $contentReferenceA */
            $contentReferenceA = $a['object'];
            /** @var \Darvin\ContentBundle\Entity\ContentReference $contentReferenceB */
            $contentReferenceB = $b['object'];

            $classA = $entityResolver->resolve($contentReferenceA->getObjectClass());
            $classB = $entityResolver->resolve($contentReferenceB->getObjectClass());

            if ($classA !== $classB) {
                return $classA > $classB ? 1 : -1;
            }

            $sortableConfig = $sortableListener->getConfiguration($em, $classA);

            if (empty($sortableConfig)) {
                return 0;
            }

            $positionA = $propertyAccessor->getValue($contentReferenceA->getObject(), $sortableConfig['position']);
            $positionB = $propertyAccessor->getValue($contentReferenceB->getObject(), $sortableConfig['position']);

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
     * @param \Darvin\ContentBundle\Entity\ContentReference $contentReference Content reference
     *
     * @return bool
     */
    private function isContentReferenceActive(ContentReference $contentReference): bool
    {
        $customObject = $contentReference->getObject();

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
            ->setAllowedValues('menu', array_map(function (Menu $menu): string {
                return $menu->getName();
            }, $this->menuProvider->getMenuCollection()))
            ->setNormalizer('depth', function (Options $options, $depth): ?int {
                if (null !== $depth) {
                    $depth = (int)$depth;
                }

                return $depth;
            });
    }

    /**
     * @return \Darvin\ContentBundle\Repository\ContentReferenceRepository
     */
    private function getContentReferenceRepository(): ContentReferenceRepository
    {
        return $this->em->getRepository(ContentReference::class);
    }

    /**
     * @return \Darvin\MenuBundle\Repository\MenuEntryRepository
     */
    private function getMenuEntryRepository(): MenuEntryRepository
    {
        return $this->em->getRepository(MenuEntryInterface::class);
    }
}
