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

use Darvin\ImageBundle\Entity\Image\AbstractImage;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Item factory abstract implementation
 */
abstract class AbstractItemFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    protected $genericItemFactory;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected $extrasResolver;

    /**
     * @param \Doctrine\ORM\EntityManager $em                 Entity manager
     * @param \Knp\Menu\FactoryInterface  $genericItemFactory Generic item factory
     */
    public function __construct(EntityManager $em, FactoryInterface $genericItemFactory)
    {
        $this->em = $em;
        $this->genericItemFactory = $genericItemFactory;

        $extrasResolver = new OptionsResolver();
        $this->configureExtras($extrasResolver);
        $this->extrasResolver = $extrasResolver;
    }

    /**
     * @param object $entity Entity
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createItem($entity)
    {
        $this->validateEntity($entity);

        return $this->create($entity, $this->getOptions($entity));
    }

    /**
     * @return \Knp\Menu\FactoryInterface
     */
    public function getGenericItemFactory()
    {
        return $this->genericItemFactory;
    }

    /**
     * @param object $entity Entity
     *
     * @return string
     */
    abstract protected function getLabel($entity);

    /**
     * @param object $entity Entity
     *
     * @return string
     */
    abstract protected function getUri($entity);

    /**
     * @return string
     */
    abstract protected function getSupportedClass();

    /**
     * @param object $entity  Entity
     * @param array  $options Options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function create($entity, array $options)
    {
        return $this->genericItemFactory->createItem($this->getItemName($entity), $options);
    }

    /**
     * @param object $entity Entity
     *
     * @return string
     */
    protected function getItemName($entity)
    {
        $class = ClassUtils::getClass($entity);
        $ids = $this->em->getClassMetadata($class)->getIdentifierValues($entity);

        return uniqid(sprintf('%s-%s-', $class, reset($ids)), true);
    }

    /**
     * @param object $entity Entity
     *
     * @return array
     */
    protected function getOptions($entity)
    {
        return [
            'label'  => $this->getLabel($entity),
            'uri'    => $this->getUri($entity),
            'extras' => $this->extrasResolver->resolve($this->getExtras($entity)),
        ];
    }

    /**
     * @param object $entity Entity
     *
     * @return array
     */
    protected function getExtras($entity)
    {
        return [];
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Extras resolver
     */
    protected function configureExtras(OptionsResolver $resolver)
    {
        foreach ([
            'hasSlugMapChildren',
            'showSlugMapChildren',
        ] as $extra) {
            $resolver
                ->setDefault($extra, false)
                ->setAllowedTypes($extra, 'boolean');
        }
        foreach ([
            'image',
            'hoverImage',
        ] as $extra) {
            $resolver
                ->setDefault($extra, null)
                ->setAllowedTypes($extra, [
                    AbstractImage::class,
                    'null',
                ]);
        }
    }

    /**
     * @param object $entity Entity
     *
     * @throws \Darvin\MenuBundle\Item\ItemFactoryException
     */
    protected function validateEntity($entity)
    {
        if (!is_object($entity)) {
            throw new ItemFactoryException(sprintf('Entity must be object, got "%s".', gettype($entity)));
        }

        $class = ClassUtils::getClass($entity);
        $supportedClass = $this->getSupportedClass();

        if ($class !== $supportedClass) {
            throw new ItemFactoryException(sprintf('Entity must instance of "%s", got "%s".', $supportedClass, $class));
        }
    }
}
