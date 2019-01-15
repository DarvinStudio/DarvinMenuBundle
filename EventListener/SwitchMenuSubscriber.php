<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\EventListener;

use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Translatable\TranslationsInitializerInterface;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Switcher\MenuSwitcher;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Switch menu event subscriber
 */
class SwitchMenuSubscriber implements EventSubscriber
{
    /**
     * @var \Darvin\MenuBundle\Switcher\MenuSwitcher
     */
    private $menuSwitcher;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationsInitializerInterface
     */
    private $translationsInitializer;

    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Darvin\MenuBundle\Switcher\MenuSwitcher                            $menuSwitcher            Menu switcher
     * @param \Darvin\ContentBundle\Translatable\TranslationsInitializerInterface $translationsInitializer Translations initializer
     * @param string[]                                                            $locales                 Locales
     */
    public function __construct(MenuSwitcher $menuSwitcher, TranslationsInitializerInterface $translationsInitializer, array $locales)
    {
        $this->menuSwitcher = $menuSwitcher;
        $this->translationsInitializer = $translationsInitializer;
        $this->locales = $locales;

        $this->em = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
            Events::preFlush,
        ];
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $args Event arguments
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $computeChangeSets = false;

        foreach ($uow->getScheduledEntityInsertions() as $slugMapItem) {
            if (!$slugMapItem instanceof SlugMapItem) {
                continue;
            }
            foreach ($this->menuSwitcher->getMenusToEnable() as $menuAlias => $entities) {
                foreach ($entities as $entity) {
                    if ($slugMapItem->getObjectClass() === get_class($entity)
                        && $slugMapItem->getObjectId() === $this->getEntityId($entity)
                    ) {
                        $em->persist($this->createMenuItem($menuAlias, $slugMapItem));

                        $computeChangeSets = true;
                    }
                }
            }
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$this->menuSwitcher->hasEnabledMenus($entity)) {
                continue;
            }
            foreach ($this->getMenuItems($entity) as $menuItem) {
                $em->remove($menuItem);
            }
        }
        if ($computeChangeSets) {
            $uow->computeChangeSets();
        }
    }

    /**
     * @param \Doctrine\ORM\Event\PreFlushEventArgs $args Event arguments
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        $this->em = $em = $args->getEntityManager();

        foreach ($this->menuSwitcher->getMenusToEnable() as $menuAlias => $entities) {
            foreach ($entities as $entity) {
                $slugMapItem = $this->getSlugMapItem($entity);

                if (!empty($slugMapItem)) {
                    $em->persist($this->createMenuItem($menuAlias, $slugMapItem));
                }
            }
        }
        foreach ($this->menuSwitcher->getMenusToDisable() as $menuAlias => $entities) {
            foreach ($entities as $entity) {
                foreach ($this->getMenuItems($entity, $menuAlias) as $menuItem) {
                    $em->remove($menuItem);
                }
            }
        }
    }

    /**
     * @param string                                   $menuAlias   Menu alias
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem Slug map item
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item
     */
    private function createMenuItem($menuAlias, SlugMapItem $slugMapItem)
    {
        $menuItem = (new Item())
            ->setMenu($menuAlias)
            ->setSlugMapItem($slugMapItem);

        $this->translationsInitializer->initializeTranslations($menuItem, $this->locales);

        return $menuItem;
    }

    /**
     * @param object      $entity    Entity
     * @param string|null $menuAlias Menu alias
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    private function getMenuItems($entity, $menuAlias = null)
    {
        return $this->getMenuItemRepository()->getByEntity(get_class($entity), $this->getEntityId($entity), $menuAlias);
    }

    /**
     * @param object $entity Entity
     *
     * @return \Darvin\ContentBundle\Entity\SlugMapItem|null
     */
    private function getSlugMapItem($entity)
    {
        return $this->getSlugMapItemRepository()->createQueryBuilder('o')
            ->andWhere('o.objectClass = :entity_class')
            ->setParameter('entity_class', get_class($entity))
            ->andWhere('o.objectId = :entity_id')
            ->setParameter('entity_id', $this->getEntityId($entity))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param object $entity Entity
     *
     * @return mixed
     */
    private function getEntityId($entity)
    {
        $ids = $this->em->getClassMetadata(get_class($entity))->getIdentifierValues($entity);

        return reset($ids);
    }

    /**
     * @return \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private function getMenuItemRepository()
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
