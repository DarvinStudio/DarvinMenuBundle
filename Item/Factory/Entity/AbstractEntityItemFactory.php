<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item\Factory\Entity;

use Darvin\ContentBundle\Router\SlugMapRouterInterface;
use Darvin\MenuBundle\Item\Factory\AbstractItemFactory;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

/**
 * Entity KNP menu item factory abstract implementation
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
     * @var \Darvin\ContentBundle\Router\SlugMapRouterInterface
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
     * @param \Darvin\ContentBundle\Router\SlugMapRouterInterface $slugMapRouter Slug map router
     */
    public function setSlugMapRouter(SlugMapRouterInterface $slugMapRouter): void
    {
        $this->slugMapRouter = $slugMapRouter;
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
