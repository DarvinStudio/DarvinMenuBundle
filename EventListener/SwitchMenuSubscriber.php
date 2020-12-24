<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\EventListener;

use Darvin\ContentBundle\Entity\ContentReference;
use Darvin\ContentBundle\Repository\ContentReferenceRepository;
use Darvin\ContentBundle\Translatable\TranslationInitializerInterface;
use Darvin\MenuBundle\Entity\MenuEntry;
use Darvin\MenuBundle\Entity\MenuEntryInterface;
use Darvin\MenuBundle\Repository\MenuEntryRepository;
use Darvin\MenuBundle\Switcher\MenuSwitcherInterface;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Switch menu event subscriber
 */
class SwitchMenuSubscriber implements EventSubscriber
{
    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Darvin\MenuBundle\Switcher\MenuSwitcherInterface
     */
    private $menuSwitcher;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationInitializerInterface
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
     * @param \Darvin\Utils\ORM\EntityResolverInterface                          $entityResolver         Entity resolver
     * @param \Darvin\MenuBundle\Switcher\MenuSwitcherInterface                  $menuSwitcher           Menu switcher
     * @param \Darvin\ContentBundle\Translatable\TranslationInitializerInterface $translationInitializer Translation initializer
     * @param string[]                                                           $locales                Locales
     */
    public function __construct(
        EntityResolverInterface $entityResolver,
        MenuSwitcherInterface $menuSwitcher,
        TranslationInitializerInterface $translationInitializer,
        array $locales
    ) {
        $this->entityResolver = $entityResolver;
        $this->menuSwitcher = $menuSwitcher;
        $this->translationInitializer = $translationInitializer;
        $this->locales = $locales;

        $this->em = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::preFlush,
        ];
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $args Event arguments
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $this->em = $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $computeChangeSets = false;

        foreach ($uow->getScheduledEntityInsertions() as $contentReference) {
            if (!$contentReference instanceof ContentReference) {
                continue;
            }
            foreach ($this->menuSwitcher->getMenusToEnable() as $menu => $entities) {
                foreach ($entities as $entity) {
                    if ($contentReference->getObjectClass() === ClassUtils::getClass($entity)
                        && (string)$contentReference->getObjectId() === (string)$this->getEntityId($entity)
                    ) {
                        $em->persist($this->createEntry($menu, $contentReference));

                        $computeChangeSets = true;
                    }
                }
            }
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$this->menuSwitcher->hasEnabledMenus($entity)) {
                continue;
            }
            foreach ($this->getEntries($entity) as $entry) {
                $em->remove($entry);
            }
        }
        if ($computeChangeSets) {
            $uow->computeChangeSets();
        }
    }

    /**
     * @param \Doctrine\ORM\Event\PreFlushEventArgs $args Event arguments
     */
    public function preFlush(PreFlushEventArgs $args): void
    {
        $this->em = $em = $args->getEntityManager();

        foreach ($this->menuSwitcher->getMenusToEnable() as $menu => $entities) {
            foreach ($entities as $entity) {
                $contentReference = $this->getContentReference($entity);

                if (null !== $contentReference) {
                    $em->persist($this->createEntry($menu, $contentReference));
                }
            }
        }
        foreach ($this->menuSwitcher->getMenusToDisable() as $menu => $entities) {
            foreach ($entities as $entity) {
                foreach ($this->getEntries($entity, $menu) as $entry) {
                    $em->remove($entry);
                }
            }
        }
    }

    /**
     * @param string                                        $menu             Menu name
     * @param \Darvin\ContentBundle\Entity\ContentReference $contentReference Content reference
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntry
     */
    private function createEntry(string $menu, ContentReference $contentReference): MenuEntry
    {
        $class = $this->entityResolver->resolve(MenuEntryInterface::class);

        /** @var \Darvin\MenuBundle\Entity\MenuEntry $entry */
        $entry = new $class();
        $entry
            ->setContentReference($contentReference)
            ->setMenu($menu);

        $this->translationInitializer->initializeTranslations($entry, $this->locales);

        return $entry;
    }

    /**
     * @param object      $entity Entity
     * @param string|null $menu   Menu name
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntry[]
     */
    private function getEntries(object $entity, ?string $menu = null): array
    {
        $class = ClassUtils::getClass($entity);

        return $this->getMenuEntryRepository()->getForSwitchMenuSubscriber(
            [$class, $this->entityResolver->reverseResolve($class)],
            $this->getEntityId($entity),
            $menu
        );
    }

    /**
     * @param object $entity Entity
     *
     * @return \Darvin\ContentBundle\Entity\ContentReference|null
     */
    private function getContentReference(object $entity): ?ContentReference
    {
        $class = ClassUtils::getClass($entity);

        return $this->getContentReferenceRepository()->getOneByClassesAndId(
            [$class, $this->entityResolver->reverseResolve($class)],
            $this->getEntityId($entity)
        );
    }

    /**
     * @param object $entity Entity
     *
     * @return mixed
     */
    private function getEntityId(object $entity)
    {
        $ids = $this->em->getClassMetadata(ClassUtils::getClass($entity))->getIdentifierValues($entity);

        return reset($ids);
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
