<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
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
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Item\Factory\Pool\ItemFactoryPoolInterface;
use Darvin\MenuBundle\Repository\Menu\ItemRepository;
use Darvin\MenuBundle\Slug\SlugMapObjectLoaderInterface;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\ORM\EntityManager;
use Gedmo\Sortable\SortableListener;
use Knp\Menu\ItemInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Builder
 */
class Builder implements MenuBuilderInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Darvin\MenuBundle\Item\Factory\Pool\ItemFactoryPoolInterface
     */
    private $itemFactoryPool;

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
     * @var \Darvin\MenuBundle\Slug\SlugMapObjectLoaderInterface
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
     * @var string
     */
    private $menuAlias;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver|null
     */
    private $optionsResolver;

    /**
     * @var array
     */
    private $slugPartSeparators;

    /**
     * @param \Doctrine\ORM\EntityManager                                   $em                  Entity manager
     * @param \Darvin\Utils\ORM\EntityResolverInterface                     $entityResolver      Entity resolver
     * @param \Darvin\MenuBundle\Item\Factory\Pool\ItemFactoryPoolInterface $itemFactoryPool     Item factory pool
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                  $localeProvider      Locale provider
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                $metadataFactory     Extended metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface   $propertyAccessor    Property accessor
     * @param \Darvin\MenuBundle\Slug\SlugMapObjectLoaderInterface          $slugMapObjectLoader Slug map object loader
     * @param \Gedmo\Sortable\SortableListener                              $sortableListener    Sortable event listener
     * @param array                                                         $entityConfig        Entity configuration
     */
    public function __construct(
        EntityManager $em,
        EntityResolverInterface $entityResolver,
        ItemFactoryPoolInterface $itemFactoryPool,
        LocaleProviderInterface $localeProvider,
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor,
        SlugMapObjectLoaderInterface $slugMapObjectLoader,
        SortableListener $sortableListener,
        array $entityConfig
    ) {
        $this->em = $em;
        $this->entityResolver = $entityResolver;
        $this->itemFactoryPool = $itemFactoryPool;
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
     * @param string $menuAlias Menu alias
     */
    public function setMenuAlias(string $menuAlias): void
    {
        $this->menuAlias = $menuAlias;
    }

    /**
     * {@inheritDoc}
     */
    public function buildMenu(array $options = []): ItemInterface
    {
        $options = $this->getOptionsResolver()->resolve($options);

        $root = $this->itemFactoryPool->createItem($this->menuAlias);

        $entities = $this->getMenuItemEntities();

        $this->addItems($root, $entities);

        return $root;
    }

    /**
     * @param \Knp\Menu\ItemInterface               $root     Root item
     * @param \Darvin\MenuBundle\Entity\Menu\Item[] $entities Menu item entities
     */
    private function addItems(ItemInterface $root, array $entities): void
    {
        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = $parentSlugs = $separatorCounts = [];

        foreach ($entities as $key => $entity) {
            $slugMapItem = $entity->getSlugMapItem();

            if (!empty($slugMapItem)) {
                $separator = $this->getSlugPartsSeparator($slugMapItem->getObjectClass(), $slugMapItem->getProperty());

                if (false === $separator) {
                    unset($entities[$key]);

                    continue;
                }
                if ($entity->isShowChildren()) {
                    $parentSlug = $slugMapItem->getSlug().$separator;
                    $parentSlugs[$entity->getId()] = $parentSlug;

                    $separatorCounts[$entity->getId()] = substr_count($parentSlug, $separator);
                }
            }

            $item = $this->itemFactoryPool->createItem($entity);
            $items[$entity->getId()] = $item;

            if (null === $entity->getParent()) {
                $root->addChild($item);

                continue;
            }

            $parentId = $entity->getParent()->getId();

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
                $this->addChildren($items[$entityId], $separatorCounts[$entityId], $childSlugMapItems);
            }
        }
    }

    /**
     * @param \Knp\Menu\ItemInterface                    $parent            Parent item
     * @param int                                        $separatorCount    Count of separators in the parent item's slug
     * @param \Darvin\ContentBundle\Entity\SlugMapItem[] $childSlugMapItems Child slug map items
     */
    private function addChildren(ItemInterface $parent, int $separatorCount, array $childSlugMapItems): void
    {
        $childSlugMapItems = $this->prepareChildSlugMapItems($childSlugMapItems);

        $parent->setExtras(array_merge($parent->getExtras(), [
            'hasSlugMapChildren' => !empty($childSlugMapItems),
        ]));

        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = [];

        foreach ($childSlugMapItems as $id => $slugMapItem) {
            $item = $this->itemFactoryPool->createItem($slugMapItem['object']);
            $items[$id] = $item;

            $parentId = $slugMapItem['parent_id'];

            if (empty($parentId)) {
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
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    private function getMenuItemEntities(): array
    {
        $entities = $this->getEntityRepository()->getForMenuBuilder($this->menuAlias, $this->localeProvider->getCurrentLocale());

        if (empty($entities)) {
            return $entities;
        }

        $slugMapItems = [];

        /** @var \Darvin\MenuBundle\Entity\Menu\Item $entity */
        foreach ($entities as $entity) {
            if (null !== $entity->getSlugMapItem()) {
                $slugMapItems[$entity->getId()] = $entity->getSlugMapItem();
            }
        }

        $this->slugMapObjectLoader->loadObjects($slugMapItems);

        foreach ($entities as $key => $entity) {
            if (null !== $entity->getSlugMapItem() && !$this->isSlugMapItemActive($entity->getSlugMapItem())) {
                unset($entities[$key]);
            }
        }

        return $entities;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem[] $childSlugMapItems Child slug map items
     *
     * @return array
     */
    private function prepareChildSlugMapItems(array $childSlugMapItems): array
    {
        $children = [];

        if (empty($childSlugMapItems)) {
            return $children;
        }

        $separator = $this->getSlugPartsSeparator($childSlugMapItems[0]->getObjectClass(), $childSlugMapItems[0]->getProperty());

        if (false === $separator) {
            return $children;
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
                'separator_count' => substr_count($slugMapItem->getSlug(), $separator) + 1,
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

            return $positionA === $positionB ? 0 : ($positionA > $positionB ? 1 : -1);
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

        if (empty($customObject)) {
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
            ->setDefault('depth', null)
            ->setAllowedTypes('depth', ['integer', 'null']);
    }

    /**
     * @return \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private function getEntityRepository(): ItemRepository
    {
        return $this->em->getRepository(Item::class);
    }

    /**
     * @return \Darvin\ContentBundle\Repository\SlugMapItemRepository
     */
    private function getSlugMapItemRepository(): SlugMapItemRepository
    {
        return $this->em->getRepository(SlugMapItem::class);
    }
}
