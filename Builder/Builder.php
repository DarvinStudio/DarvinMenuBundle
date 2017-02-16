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

use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Item\MenuItemFactory;
use Darvin\MenuBundle\Item\SlugMapItemFactory;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Darvin\Utils\Locale\LocaleProviderInterface;
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
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var \Darvin\MenuBundle\Item\MenuItemFactory
     */
    protected $menuItemFactory;

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
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface        $customObjectLoader Custom object loader
     * @param \Doctrine\ORM\EntityManager                                   $em                 Entity manager
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                  $localeProvider     Locale provider
     * @param \Darvin\MenuBundle\Item\MenuItemFactory                       $menuItemFactory    Item from menu item entity factory
     * @param \Darvin\MenuBundle\Item\SlugMapItemFactory                    $slugMapItemFactory Item from slug map item factory
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner  Translation joiner
     * @param string                                                        $menuAlias          Menu alias
     */
    public function __construct(
        CustomObjectLoaderInterface $customObjectLoader,
        EntityManager $em,
        LocaleProviderInterface $localeProvider,
        MenuItemFactory $menuItemFactory,
        SlugMapItemFactory $slugMapItemFactory,
        TranslationJoinerInterface $translationJoiner,
        $menuAlias
    ) {
        $this->customObjectLoader = $customObjectLoader;
        $this->em = $em;
        $this->localeProvider = $localeProvider;
        $this->menuItemFactory = $menuItemFactory;
        $this->slugMapItemFactory = $slugMapItemFactory;
        $this->translationJoiner = $translationJoiner;
        $this->menuAlias = $menuAlias;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu()
    {
        $root = $this->menuItemFactory->getGenericItemFactory()->createItem($this->menuAlias);

        $entities = $this->getEntities();

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
        $items = [];

        foreach ($entities as $entity) {
            $item = $this->menuItemFactory->createItem($entity);

            if ($entity->isShowChildren() && null !== $entity->getSlugMapItem()) {
                $this->addChildren($item, $entity->getSlugMapItem());
            }

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
    }

    /**
     * @param \Knp\Menu\ItemInterface                  $parent      Parent item
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     */
    protected function addChildren(ItemInterface $parent, SlugMapItem $slugMapItem)
    {
        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = [];

        foreach ($this->getSlugMapItemChildren($slugMapItem) as $id => $slugMapItem) {
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
    protected function getEntities()
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

        return $entities;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return \Darvin\ContentBundle\Entity\SlugMapItem[]
     */
    protected function getSlugMapItemChildren(SlugMapItem $slugMapItem)
    {
        /** @var \Darvin\ContentBundle\Entity\SlugMapItem[] $slugMapItems */
        $slugMapItems = $this->getSlugMapItemRepository()->getChildrenBuilder($slugMapItem)->getQuery()->getResult();

        $children = [];

        if (empty($slugMapItems)) {
            return $children;
        }

        $this->loadSlugMapItemCustomObjects($slugMapItems);

        foreach ($slugMapItems as $slugMapItem) {
            $children[$slugMapItem->getId()] = [
                'object'    => $slugMapItem,
                'slug'      => $slugMapItem->getSlug(),
                'level'     => substr_count($slugMapItem->getSlug(), '/'),
                'parent_id' => null,
            ];
        }
        foreach ($children as $childId => $child) {
            foreach ($children as $otherChildId => $otherChild) {
                if (1 === $child['level'] - $otherChild['level'] && 0 === strpos($child['slug'], $otherChild['slug'].'/')) {
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
        $translationJoiner = $this->translationJoiner;

        $this->customObjectLoader->loadCustomObjects($slugMapItems, function (QueryBuilder $qb) use ($locale, $translationJoiner) {
            if ($translationJoiner->isTranslatable($qb->getRootEntities()[0])) {
                $translationJoiner->joinTranslation($qb, true, $locale, null, true);
            }
        });
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
