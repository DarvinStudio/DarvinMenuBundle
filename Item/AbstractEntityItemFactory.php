<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item;

use Darvin\ContentBundle\Slug\SlugMapRouterInterface;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Doctrine\ORM\EntityManager;
use Knp\Menu\ItemInterface;

/**
 * Entity item factory abstract implementation
 */
abstract class AbstractEntityItemFactory extends AbstractItemFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamerInterface
     */
    protected $objectNamer;

    /**
     * @var \Darvin\ContentBundle\Slug\SlugMapRouterInterface
     */
    protected $slugMapRouter;

    /**
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     */
    public function setEntityManager(EntityManager $em): void
    {
        $this->em = $em;
    }

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer Object namer
     */
    public function setObjectNamer(ObjectNamerInterface $objectNamer): void
    {
        $this->objectNamer = $objectNamer;
    }

    /**
     * @param \Darvin\ContentBundle\Slug\SlugMapRouterInterface $slugMapRouter Slug map router
     */
    public function setSlugMapRouter(SlugMapRouterInterface $slugMapRouter): void
    {
        $this->slugMapRouter = $slugMapRouter;
    }

    /**
     * {@inheritDoc}
     */
    public function createItem($source): ItemInterface
    {
        $this->validateEntity($source);

        return parent::createItem($source);
    }

    /**
     * {@inheritDoc}
     */
    protected function nameItem($source): ?string
    {
        $entity = $source;

        $class = get_class($entity);

        $ids = $this->em->getClassMetadata($class)->getIdentifierValues($entity);

        return uniqid(sprintf('%s-%s-', $class, reset($ids)), true);
    }

    /**
     * @return string
     */
    abstract protected function getSupportedClass(): string;

    /**
     * @param object $entity Entity
     *
     * @throws \InvalidArgumentException
     */
    protected function validateEntity($entity): void
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException(sprintf('Entity must be object, got "%s".', gettype($entity)));
        }

        $supportedClass = $this->getSupportedClass();

        if (!$entity instanceof $supportedClass) {
            throw new \InvalidArgumentException(
                sprintf('Entity must be instance of "%s", got "%s".', $supportedClass, get_class($entity))
            );
        }
    }
}
