<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\EventListener;

use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Hide\HidePropertyAccessor;
use Darvin\Utils\Service\ServiceProviderInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * Sync hide property event listener
 */
class SyncHidePropertyListener
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $associationConfigProvider;

    /**
     * @var \Darvin\MenuBundle\Hide\HidePropertyAccessor
     */
    private $hidePropertyAccessor;

    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $translatableManagerProvider;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\UnitOfWork
     */
    private $uow;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface $associationConfigProvider   Association configuration provider
     * @param \Darvin\MenuBundle\Hide\HidePropertyAccessor   $hidePropertyAccessor        Hide property accessor
     * @param \Darvin\Utils\Service\ServiceProviderInterface $translatableManagerProvider Translatable manager provider
     */
    public function __construct(
        ServiceProviderInterface $associationConfigProvider,
        HidePropertyAccessor $hidePropertyAccessor,
        ServiceProviderInterface $translatableManagerProvider
    ) {
        $this->associationConfigProvider = $associationConfigProvider;
        $this->hidePropertyAccessor = $hidePropertyAccessor;
        $this->translatableManagerProvider = $translatableManagerProvider;

        $this->em = $this->uow = null;
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $args Event arguments
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $em = $args->getEntityManager();
        $this->uow = $uow = $em->getUnitOfWork();

        $associationConfig = $this->getAssociationConfig();
        $translatableManager = $this->getTranslatableManager();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Item) {
                $this->updateAssociated($entity);
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Item) {
                $this->updateAssociated($entity);

                continue;
            }

            $class = ClassUtils::getClass($entity);

            if ($associationConfig->hasAssociationClass($class)) {
                $this->updateMenuItem($entity, $class);

                continue;
            }
            if (!$translatableManager->isTranslation($class)) {
                continue;
            }

            $class = $translatableManager->getTranslatableClass($class);

            if ($associationConfig->hasAssociationClass($class)) {
                /** @var \Knp\DoctrineBehaviors\Model\Translatable\Translation $entity */
                $this->updateMenuItem($entity->getTranslatable(), $class);
            }
        }
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     */
    private function updateAssociated(Item $menuItem)
    {
        $associationConfig = $this->getAssociationConfig();

        $associatedClass = $menuItem->getAssociatedClass();
        $associatedId = $menuItem->getAssociatedId();

        if (empty($associatedClass)
            || empty($associatedId)
            || !$associationConfig->hasAssociationClass($associatedClass)
            || $this->getMenuItemRepository()->isAssociatedInOtherMenus($associatedClass, $associatedId, $menuItem->getMenu(), $menuItem->getId())
        ) {
            return;
        }

        $associated = $menuItem->getAssociatedInstance();

        if (empty($associated)) {
            $associated = $this->em->find($associatedClass, $associatedId);

            if (empty($associated)) {
                return;
            }
        }

        $hidden = !$menuItem->isEnabled();

        if ($this->hidePropertyAccessor->isHidden($associated) === $hidden) {
            return;
        }

        $this->hidePropertyAccessor->setHidden($associated, !$menuItem->isEnabled());

        $this->uow->computeChangeSets();
        $this->uow->recomputeSingleEntityChangeSet($this->em->getClassMetadata($associatedClass), $associated);
    }

    /**
     * @param object $associated      Associated
     * @param string $associatedClass Associated class
     */
    private function updateMenuItem($associated, $associatedClass)
    {
        $ids = $this->em->getClassMetadata($associatedClass)->getIdentifierValues($associated);

        $menuItems = $this->getMenuItemsByAssociated($associatedClass, reset($ids));

        if (1 !== count($menuItems)) {
            return;
        }

        $menuItem = $menuItems[0];

        $enabled = !$this->hidePropertyAccessor->isHidden($associated);

        if ($menuItem->isEnabled() === $enabled) {
            return;
        }

        $menuItem->setEnabled($enabled);

        $this->uow->computeChangeSets();
    }


    /**
     * @param string $associatedClass Associated class
     * @param string $associatedId    Associated ID
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    private function getMenuItemsByAssociated($associatedClass, $associatedId)
    {
        return $this->getMenuItemRepository()->getByAssociatedBuilder($associatedClass, $associatedId)->getQuery()->getResult();
    }

    /**
     * @return \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private function getMenuItemRepository()
    {
        return $this->em->getRepository(Item::class);
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private function getAssociationConfig()
    {
        return $this->associationConfigProvider->getService();
    }

    /**
     * @return \Darvin\ContentBundle\Translatable\TranslatableManagerInterface
     */
    private function getTranslatableManager()
    {
        return $this->translatableManagerProvider->getService();
    }
}
