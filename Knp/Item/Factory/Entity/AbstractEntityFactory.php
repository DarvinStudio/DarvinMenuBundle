<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Knp\Item\Factory\Entity;

use Darvin\ContentBundle\Router\ContentReferenceRouterInterface;
use Darvin\MenuBundle\Knp\Item\Factory\AbstractFactory;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

/**
 * Entity KNP menu item factory abstract implementation
 */
abstract class AbstractEntityFactory extends AbstractFactory
{
    /**
     * @var \Darvin\ContentBundle\Router\ContentReferenceRouterInterface
     */
    protected $contentReferenceRouter;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamerInterface
     */
    protected $objectNamer;

    /**
     * @param \Darvin\ContentBundle\Router\ContentReferenceRouterInterface $contentReferenceRouter Content reference router
     */
    public function setContentReferenceRouter(ContentReferenceRouterInterface $contentReferenceRouter): void
    {
        $this->contentReferenceRouter = $contentReferenceRouter;
    }

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
     * {@inheritDoc}
     */
    protected function nameItem($source): ?string
    {
        $entity = $source;

        $class = ClassUtils::getClass($entity);

        return implode('-', array_merge([$class], $this->em->getClassMetadata($class)->getIdentifierValues($entity)));
    }
}
