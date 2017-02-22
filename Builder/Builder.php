<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Builder;

use Darvin\ContentBundle\Disableable\DisableableInterface;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Hideable\HideableInterface;
use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Darvin\ImageBundle\ORM\ImageJoinerInterface;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Item\MenuItemFactory;
use Darvin\MenuBundle\Item\RootItemFactory;
use Darvin\MenuBundle\Item\SlugMapItemFactory;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Knp\Menu\ItemInterface;

/**
 * Builder
 */
class Builder
{
    const BUILD_METHOD = 'buildMenu';

    /**
     * @var \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    protected $customObjectLoader;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Darvin\ImageBundle\ORM\ImageJoinerInterface
     */
    protected $imageJoiner;

    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var \Darvin\MenuBundle\Item\MenuItemFactory
     */
    protected $menuItemFactory;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @var \Darvin\MenuBundle\Item\RootItemFactory
     */
    protected $rootItemFactory;

    /**
     * @var \Darvin\MenuBundle\Item\SlugMapItemFactory
     */
    protected $slugMapItemFactory;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationJoinerInterface
     */
    protected $translationJoiner;

    /**
     * @var string
     */
    protected $menuAlias;

    /**
     * @var array
     */
    protected $slugPartSeparators;

    /**
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface        $customObjectLoader Custom object loader
     * @param \Doctrine\ORM\EntityManager                                   $em                 Entity manager
     * @param \Darvin\ImageBundle\ORM\ImageJoinerInterface                  $imageJoiner        Image joiner
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                  $localeProvider     Locale provider
     * @param \Darvin\MenuBundle\Item\MenuItemFactory                       $menuItemFactory    Item from menu item entity factory
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                $metadataFactory    Extended metadata factory
     * @param \Darvin\MenuBundle\Item\RootItemFactory                       $rootItemFactory    Root item factory
     * @param \Darvin\MenuBundle\Item\SlugMapItemFactory                    $slugMapItemFactory Item from slug map item entity factory
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner  Translation joiner
     * @param string                                                        $menuAlias          Menu alias
     */
    public function __construct(
        CustomObjectLoaderInterface $customObjectLoader,
        EntityManager $em,
        ImageJoinerInterface $imageJoiner,
        LocaleProviderInterface $localeProvider,
        MenuItemFactory $menuItemFactory,
        MetadataFactoryInterface $metadataFactory,
        RootItemFactory $rootItemFactory,
        SlugMapItemFactory $slugMapItemFactory,
        TranslationJoinerInterface $translationJoiner,
        $menuAlias
    ) {
        $this->customObjectLoader = $customObjectLoader;
        $this->em = $em;
        $this->imageJoiner = $imageJoiner;
        $this->localeProvider = $localeProvider;
        $this->menuItemFactory = $menuItemFactory;
        $this->metadataFactory = $metadataFactory;
        $this->rootItemFactory = $rootItemFactory;
        $this->slugMapItemFactory = $slugMapItemFactory;
        $this->translationJoiner = $translationJoiner;
        $this->menuAlias = $menuAlias;

        $this->slugPartSeparators = [];
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu()
    {
        $root = $this->rootItemFactory->createItem($this->menuAlias);

        $entities = $this->getMenuItemEntities();

        $this->addItems($root, $entities);

        return $root;
    }

    /**
     * @param \Knp\Menu\ItemInterface               $root     Root item
     * @param \Darvin\MenuBundle\Entity\Menu\Item[] $entities Menu item entities
     */
    protected function addItems(ItemInterface $root, array $entities)
    {
        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = $parentSlugs = [];

        foreach ($entities as $key => $entity) {
            $slugMapItem = $entity->getSlugMapItem();

            if (!empty($slugMapItem)) {
                $separator = $this->getSlugPartsSeparator($slugMapItem->getObjectClass(), $slugMapItem->getProperty());

                if (false === $separator) {
                    unset($entities[$key]);

                    continue;
                }

                $parentSlugs[$entity->getId()] = $slugMapItem->getSlug().$separator;
            }

            $item = $this->menuItemFactory->createItem($entity);
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
        foreach ($this->getSlugMapItemRepository()->getBySlugsChildren(array_unique($parentSlugs)) as $parentSlug => $childSlugMapItems) {
            foreach (array_keys($parentSlugs, $parentSlug) as $entityId) {
                $this->addChildren($items[$entityId], $childSlugMapItems);
            }
        }
    }

    /**
     * @param \Knp\Menu\ItemInterface                    $parent            Parent item
     * @param \Darvin\ContentBundle\Entity\SlugMapItem[] $childSlugMapItems Child slug map items
     */
    protected function addChildren(ItemInterface $parent, array $childSlugMapItems)
    {
        $childSlugMapItems = $this->prepareChildSlugMapItems($childSlugMapItems);

        $parent->setExtras(array_merge($parent->getExtras(), [
            'hasSlugMapChildren' => !empty($childSlugMapItems),
        ]));

        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = [];

        foreach ($childSlugMapItems as $id => $slugMapItem) {
            $item = $this->slugMapItemFactory->createItem($slugMapItem['object']);
            $items[$id] = $item;

            $parentId = $slugMapItem['parent_id'];

            if (empty($parentId)) {
                $parent->addChild($item);

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
    protected function getMenuItemEntities()
    {
        $entities = $this->getEntityRepository()->getForMenuBuilder($this->menuAlias, $this->localeProvider->getCurrentLocale())
            ->getQuery()
            ->getResult();

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

        $this->loadSlugMapItemCustomObjects($slugMapItems);

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
    protected function prepareChildSlugMapItems(array $childSlugMapItems)
    {
        $children = [];

        if (empty($childSlugMapItems)) {
            return $children;
        }

        $separator = $this->getSlugPartsSeparator($childSlugMapItems[0]->getObjectClass(), $childSlugMapItems[0]->getProperty());

        if (false === $separator) {
            return $children;
        }

        $this->loadSlugMapItemCustomObjects($childSlugMapItems);

        foreach ($childSlugMapItems as $key => $slugMapItem) {
            if (!$this->isSlugMapItemActive($slugMapItem)) {
                unset($childSlugMapItems[$key]);

                continue;
            }

            $children[$slugMapItem->getId()] = [
                'object'    => $slugMapItem,
                'slug'      => $slugMapItem->getSlug(),
                'level'     => substr_count($slugMapItem->getSlug(), $separator),
                'parent_id' => null,
            ];
        }
        foreach ($children as $childId => $child) {
            foreach ($children as $otherChildId => $otherChild) {
                if (1 === $child['level'] - $otherChild['level'] && 0 === strpos($child['slug'], $otherChild['slug'].$separator)) {
                    $children[$childId]['parent_id'] = $otherChildId;
                }
            }
        }

        uasort($children, function (array $a, array $b) {
            return $a['level'] === $b['level'] ? 0 : ($a['level'] > $b['level'] ? 1 : -1);
        });

        return $children;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem[] $slugMapItems Slug map items
     */
    protected function loadSlugMapItemCustomObjects(array $slugMapItems)
    {
        if (empty($slugMapItems)) {
            return;
        }

        $locale = $this->localeProvider->getCurrentLocale();
        $imageJoiner = $this->imageJoiner;
        $translationJoiner = $this->translationJoiner;

        $this->customObjectLoader->loadCustomObjects($slugMapItems, function (QueryBuilder $qb) use ($locale, $imageJoiner, $translationJoiner) {
            $imageJoiner->joinImages($qb);

            if ($translationJoiner->isTranslatable($qb->getRootEntities()[0])) {
                $translationJoiner->joinTranslation($qb, true, $locale, null, true);
            }
        });
    }

    /**
     * @param string $class    Class
     * @param string $property Property
     *
     * @return bool|string
     */
    protected function getSlugPartsSeparator($class, $property)
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
    protected function isSlugMapItemActive(SlugMapItem $slugMapItem)
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
     * @return \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    protected function getEntityRepository()
    {
        return $this->em->getRepository(Item::class);
    }

    /**
     * @return \Darvin\ContentBundle\Repository\SlugMapItemRepository
     */
    protected function getSlugMapItemRepository()
    {
        return $this->em->getRepository(SlugMapItem::class);
    }
}
