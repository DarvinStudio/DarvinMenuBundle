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
use Knp\Menu\FactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Item factory abstract implementation
 */
abstract class AbstractItemFactory
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    protected $genericItemFactory;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected $extrasResolver;

    /**
     * @param \Knp\Menu\FactoryInterface $genericItemFactory Generic item factory
     */
    public function __construct(FactoryInterface $genericItemFactory)
    {
        $this->genericItemFactory = $genericItemFactory;

        $extrasResolver = new OptionsResolver();
        $this->configureExtras($extrasResolver);
        $this->extrasResolver = $extrasResolver;
    }

    /**
     * @param mixed $source Source
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createItem($source)
    {
        return $this->genericItemFactory->createItem($this->getItemName($source), $this->getOptions($source));
    }

    /**
     * @param mixed $source Source
     *
     * @return string
     */
    abstract protected function getItemName($source);

    /**
     * @param mixed $source Source
     *
     * @return array
     */
    protected function getOptions($source)
    {
        return [
            'label'  => $this->getLabel($source),
            'uri'    => $this->getUri($source),
            'extras' => $this->extrasResolver->resolve($this->getExtras($source)),
        ];
    }

    /**
     * @param mixed $source Source
     *
     * @return string
     */
    protected function getLabel($source)
    {
        return null;
    }

    /**
     * @param mixed $source Source
     *
     * @return string
     */
    protected function getUri($source)
    {
        return null;
    }

    /**
     * @param mixed $source Source
     *
     * @return array
     */
    protected function getExtras($source)
    {
        return [];
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Extras resolver
     */
    protected function configureExtras(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'objectName' => null,
                'objectId'   => null,
            ])
            ->setAllowedTypes('objectName', [
                'string',
                'null',
            ])
            ->setAllowedTypes('objectId', [
                'integer',
                'string',
                'null',
            ]);

        foreach ([
            'hasSlugMapChildren',
            'isSlugMapItem',
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
}
