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
abstract class AbstractItemFactory implements ItemFactoryInterface
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
     * {@inheritdoc}
     */
    public function createItem($entity)
    {
        $class = ClassUtils::getClass($entity);

        if (!$this->supportsClass($class)) {
            throw new ItemFactoryException(
                sprintf('Unable to create menu item from instance of "%s": class is not supported.', $class)
            );
        }

        return $this->genericItemFactory->createItem($this->getItemName($entity), $this->getOptions($entity));
    }

    /**
     * @param object $entity Entity
     *
     * @return string
     */
    protected function getItemName($entity)
    {
        $class = ClassUtils::getClass($entity);

        return uniqid($class.'-'.$this->em->getClassMetadata($class)->getIdentifierValues($entity)[0], true);
    }

    /**
     * @param object $entity Entity
     *
     * @return array
     */
    protected function getOptions($entity)
    {
        return [
            'extras' => $this->extrasResolver->resolve($this->getExtras($entity)),
            'label'  => $this->getLabel($entity),
            'uri'    => $this->getUri($entity),
        ];
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Extras resolver
     */
    protected function configureExtras(OptionsResolver $resolver)
    {
        foreach ([
            'image',
            'hoverImage',
        ] as $extra) {
            $resolver
                ->setDefault($extra, null)
                ->setAllowedTypes($extra, [
                    AbstractImage::class,
                    null,
                ]);
        }
    }

    /**
     * @param object $entity Entity
     *
     * @return array
     */
    abstract protected function getExtras($entity);

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
}
