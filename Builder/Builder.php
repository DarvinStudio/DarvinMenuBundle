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

use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Knp\Menu\FactoryInterface;
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
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner  Translation joiner
     * @param string                                                        $menuAlias          Menu alias
     */
    public function __construct(
        CustomObjectLoaderInterface $customObjectLoader,
        EntityManager $em,
        FactoryInterface $genericItemFactory,
        LocaleProviderInterface $localeProvider,
        TranslationJoinerInterface $translationJoiner,
        $menuAlias
    ) {
        $this->customObjectLoader = $customObjectLoader;
        $this->em = $em;
        $this->genericItemFactory = $genericItemFactory;
        $this->localeProvider = $localeProvider;
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
        return $this->genericItemFactory->createItem($entity->getId(), $this->getItemOptions($entity));
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $entity Menu item entity
     *
     * @return array
     */
    private function getItemOptions(Item $entity)
    {
        $options = [
            'extras' => $this->getItemExtras($entity),
        ];

        return $options;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $entity Menu item entity
     *
     * @return array
     */
    private function getItemExtras(Item $entity)
    {
        return [
            'image'      => $entity->getImage(),
            'hoverImage' => $entity->getHoverImage(),
        ];
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

        foreach ($entities as $key => $entity) {
            if (null !== $entity->getSlugMapItem() && null === $entity->getSlugMapItem()->getObject()) {
                unset($entities[$key]);
            }
        }

        return $entities;
    }

    /**
     * @return \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private function getEntityRepository()
    {
        return $this->em->getRepository(Item::class);
    }
}
