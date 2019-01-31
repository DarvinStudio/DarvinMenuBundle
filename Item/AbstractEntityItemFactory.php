<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item;

use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;

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
     * @param \Knp\Menu\FactoryInterface                     $genericItemFactory Generic item factory
     * @param \Doctrine\ORM\EntityManager                    $em                 Entity manager
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer        Object namer
     */
    public function __construct(FactoryInterface $genericItemFactory, EntityManager $em, ObjectNamerInterface $objectNamer)
    {
        parent::__construct($genericItemFactory);

        $this->em = $em;
        $this->objectNamer = $objectNamer;
    }

    /**
     * @param object $entity Entity
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createItem($entity)
    {
        $this->validateEntity($entity);

        return parent::createItem($entity);
    }

    /**
     * @return string
     */
    abstract protected function getSupportedClass();

    /**
     * @param object $entity Entity
     *
     * @return string
     */
    protected function getItemName($entity)
    {
        $class = get_class($entity);
        $ids = $this->em->getClassMetadata($class)->getIdentifierValues($entity);

        return uniqid(sprintf('%s-%s-', $class, reset($ids)), true);
    }

    /**
     * @param object $entity Entity
     *
     * @throws \InvalidArgumentException
     */
    protected function validateEntity($entity)
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
