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
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Builder
 */
class Builder
{
    const BUILD_METHOD = 'buildMenu';

    /**
     * @var \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    private $customObjectLoader;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $genericItemFactory;

    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationJoinerInterface
     */
    private $translationJoiner;

    /**
     * @var string
     */
    private $menuAlias;

    /**
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface        $customObjectLoader Custom object loader
     * @param \Doctrine\ORM\EntityManager                                   $em                 Entity manager
     * @param \Knp\Menu\FactoryInterface                                    $genericItemFactory Generic item factory
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                  $localeProvider     Locale provider
     * @param \Symfony\Component\Routing\RouterInterface                    $router             Router
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner  Translation joiner
     * @param string                                                        $menuAlias          Menu alias
     */
    public function __construct(
        CustomObjectLoaderInterface $customObjectLoader,
        EntityManager $em,
        FactoryInterface $genericItemFactory,
        LocaleProviderInterface $localeProvider,
        RouterInterface $router,
        TranslationJoinerInterface $translationJoiner,
        $menuAlias
    ) {
        $this->customObjectLoader = $customObjectLoader;
        $this->em = $em;
        $this->genericItemFactory = $genericItemFactory;
        $this->localeProvider = $localeProvider;
        $this->router = $router;
        $this->translationJoiner = $translationJoiner;
        $this->menuAlias = $menuAlias;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu()
    {
        $root = $this->genericItemFactory->createItem($this->menuAlias);

        $entities = $this->getEntities();

        $this->addItems($root, $entities);

        return $root;
    }

    /**
     * @param \Knp\Menu\ItemInterface               $root     Root menu item
     * @param \Darvin\MenuBundle\Entity\Menu\Item[] $entities Menu item entities
     */
    private function addItems(ItemInterface $root, array $entities)
    {
        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = [];

        foreach ($entities as $entity) {
            $item = $this->createItem($entity);
            $items[$entity->getId()] = $item;

            if (null === $entity->getParent()) {
                $root->addChild($item);

                continue;
            }
            if (isset($items[$entity->getParent()->getId()])) {
                $items[$entity->getParent()->getId()]->addChild($item);
            }
        }
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $entity Menu item entity
     *
     * @return \Knp\Menu\ItemInterface
     */
    private function createItem(Item $entity)
    {
        $item = $this->genericItemFactory->createItem($entity->getId(), $this->getItemOptionsFromEntity($entity));

        if (null !== $entity->getSlugMapItem()) {
            $this->addChildItems($item, $entity->getSlugMapItem());
        }

        return $item;
    }

    /**
     * @param \Knp\Menu\ItemInterface                  $parent      Parent menu item
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     */
    private function addChildItems(ItemInterface $parent, SlugMapItem $slugMapItem)
    {
        /** @var \Knp\Menu\ItemInterface[] $items */
        $items = [];

        foreach ($this->getChildSlugMapItems($slugMapItem) as $id => $slugMapItem) {
            $item = $this->genericItemFactory->createItem($slugMapItem['slug'], [
                'extras' => [
                    'image'      => null,
                    'hoverImage' => null,
                ],
            ]);
            $items[$id] = $item;

            if (empty($slugMapItem['parent_id'])) {
                $parent->addChild($item);

                continue;
            }
            if (isset($items[$slugMapItem['parent_id']])) {
                $items[$slugMapItem['parent_id']]->addChild($item);
            }
        }
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $entity Menu item entity
     *
     * @return array
     */
    private function getItemOptionsFromEntity(Item $entity)
    {
        $options = [
            'extras' => $this->getItemExtrasFromEntity($entity),
            'label'  => $this->getItemLabelFromEntity($entity),
            'uri'    => $this->getItemUriFromEntity($entity),
        ];

        return $options;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $entity Menu item entity
     *
     * @return array
     */
    private function getItemExtrasFromEntity(Item $entity)
    {
        return [
            'image'      => $entity->getImage(),
            'hoverImage' => $entity->getHoverImage(),
        ];
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $entity Menu item entity
     *
     * @return string
     */
    private function getItemLabelFromEntity(Item $entity)
    {
        $title = $entity->getTitle();

        return !empty($title) ? $title : (string) $entity->getSlugMapItem()->getObject();
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $entity Menu item entity
     *
     * @return string
     */
    private function getItemUriFromEntity(Item $entity)
    {
        $url = $entity->getUrl();

        return !empty($url)
            ? $url
            : $this->router->generate('darvin_content_content_show', [
                'slug' => $entity->getSlugMapItem()->getSlug(),
            ]);
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    private function getEntities()
    {
        $entities = $this->getEntityRepository()->getForMenuBuilder($this->menuAlias, $this->localeProvider->getCurrentLocale())
            ->getQuery()
            ->getResult();
        $slugMapItems = [];

        /** @var \Darvin\MenuBundle\Entity\Menu\Item $entity */
        foreach ($entities as $entity) {
            if (null !== $entity->getSlugMapItem()) {
                $slugMapItems[$entity->getId()] = $entity->getSlugMapItem();
            }
        }

        $locale = $this->localeProvider->getCurrentLocale();
        $translationJoiner = $this->translationJoiner;

        $this->customObjectLoader->loadCustomObjects($slugMapItems, function (QueryBuilder $qb) use ($locale, $translationJoiner) {
            if ($translationJoiner->isTranslatable($qb->getRootEntities()[0])) {
                $translationJoiner->joinTranslation($qb, true, $locale, null, true);
            }
        });

        return $entities;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return \Darvin\ContentBundle\Entity\SlugMapItem[]
     */
    private function getChildSlugMapItems(SlugMapItem $slugMapItem)
    {
        /** @var \Darvin\ContentBundle\Entity\SlugMapItem[] $result */
        $result = $this->getSlugMapItemRepository()->getChildrenBuilder($slugMapItem)->getQuery()->getResult();

        $items = [];

        if (empty($result)) {
            return $items;
        }
        foreach ($result as $item) {
            $items[$item->getId()] = [
                'object'    => $item,
                'level'     => substr_count($item->getSlug(), '/'),
                'parent_id' => null,
                'slug'      => $item->getSlug(),
            ];
        }
        foreach ($items as $itemId => $item) {
            foreach ($items as $otherItemId => $otherItem) {
                if (1 === $item['level'] - $otherItem['level'] && 0 === strpos($item['slug'], $otherItem['slug'].'/')) {
                    $items[$itemId]['parent_id'] = $otherItemId;
                }
            }
        }

        uasort($items, function (array $a, array $b) {
            return $a['level'] === $b['level'] ? 0 : ($a['level'] > $b['level'] ? 1 : -1);
        });

        return $items;
    }

    /**
     * @return \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private function getEntityRepository()
    {
        return $this->em->getRepository(Item::class);
    }

    /**
     * @return \Darvin\ContentBundle\Repository\SlugMapItemRepository
     */
    private function getSlugMapItemRepository()
    {
        return $this->em->getRepository(SlugMapItem::class);
    }
}
