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
use Darvin\Utils\Service\ServiceProviderInterface;
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
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\UnitOfWork
     */
    private $uow;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface $associationConfigProvider Association configuration provider
     */
    public function __construct(ServiceProviderInterface $associationConfigProvider)
    {
        $this->associationConfigProvider = $associationConfigProvider;

        $this->em = $this->uow = null;
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $args Event arguments
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $em = $args->getEntityManager();
        $this->uow = $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Item) {
                $this->updateAssociated($entity);
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Item) {
                $this->updateAssociated($entity);
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

        if (empty($associatedClass) || empty($associatedId) || !$associationConfig->hasAssociationClass($associatedClass)) {
            return;
        }

        $associated = $menuItem->getAssociatedInstance();

        if (empty($associated)) {
            $associated = $this->em->find($associatedClass, $associatedId);

            if (empty($associated)) {
                return;
            }
        }
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private function getAssociationConfig()
    {
        return $this->associationConfigProvider->getService();
    }
}
