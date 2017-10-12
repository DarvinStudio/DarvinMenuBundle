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
use Darvin\MenuBundle\Item\MenuItemManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Manage menu items event subscriber
 */
class ManageItemsSubscriber implements EventSubscriber
{
    /**
     * @var \Darvin\MenuBundle\Item\MenuItemManager
     */
    private $menuItemManager;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationsInitializerInterface
     */
    private $translationInitializer;

    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Darvin\MenuBundle\Item\MenuItemManager                             $menuItemManager         Menu item manager
     * @param \Darvin\ContentBundle\Translatable\TranslationsInitializerInterface $translationsInitializer Translation initializer
     * @param string[]                                                            $locales                 Locales
     */
    public function __construct(MenuItemManager $menuItemManager, TranslationsInitializerInterface $translationsInitializer, array $locales)
    {
        $this->menuItemManager = $menuItemManager;
        $this->translationInitializer = $translationsInitializer;
        $this->locales = $locales;

        $this->em = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preFlush,
        ];
    }

    /**
     * @param \Doctrine\ORM\Event\PreFlushEventArgs $args Event arguments
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        $this->em = $em = $args->getEntityManager();

        foreach ($this->menuItemManager->getScheduledForAdding() as $menuAlias => $entities) {
            foreach ($entities as $entity) {
                $slugMapItem = $this->getSlugMapItem($entity);

                if (empty($slugMapItem)) {
                    continue;
                }

                $em->persist($this->createMenuItem($menuAlias, $slugMapItem));
            }
        }
        foreach ($this->menuItemManager->getScheduledForRemoval() as $menuAlias => $entities) {
            foreach ($entities as $entity) {
                $slugMapItem = $this->getSlugMapItem($entity);

                if (empty($slugMapItem)) {
                    continue;
                }

                $menuItems = $em->getRepository(Item::class)->findBy([
                    'menu'        => $menuAlias,
                    'slugMapItem' => $slugMapItem,
                ]);

                foreach ($menuItems as $menuItem) {
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
        $item = (new Item())
            ->setMenu($menuAlias)
            ->setSlugMapItem($slugMapItem);

        $this->translationInitializer->initializeTranslations($item, $this->locales);

        return $item;
    }

    /**
     * @param object $entity Entity
     *
     * @return \Darvin\ContentBundle\Entity\SlugMapItem|null
     */
    private function getSlugMapItem($entity)
    {
        $entityClass = ClassUtils::getClass($entity);
        $entityIds   = $this->em->getClassMetadata($entityClass)->getIdentifierValues($entity);

        return $this->getSlugMapItemRepository()->getByEntityBuilder($entityClass, $entityIds)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return \Darvin\ContentBundle\Repository\SlugMapItemRepository
     */
    private function getSlugMapItemRepository()
    {
        return $this->em->getRepository(SlugMapItem::class);
    }
}
