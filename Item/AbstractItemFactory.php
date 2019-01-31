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

use Darvin\ImageBundle\Entity\Image\AbstractImage;
use Darvin\MenuBundle\Entity\Menu\Item;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Item factory abstract implementation
 */
abstract class AbstractItemFactory implements ItemFactoryInterface
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    protected $genericItemFactory;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver|null
     */
    protected $extrasResolver = null;

    /**
     * @param \Knp\Menu\FactoryInterface $genericItemFactory Generic item factory
     */
    public function setGenericItemFactory(FactoryInterface $genericItemFactory): void
    {
        $this->genericItemFactory = $genericItemFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createItem($source): ItemInterface
    {
        return $this->genericItemFactory->createItem($this->nameItem($source), $this->getOptions($source));
    }

    /**
     * @param mixed $source Source
     *
     * @return string|null
     */
    protected function nameItem($source): ?string
    {
        return null;
    }

    /**
     * @param mixed $source Source
     *
     * @return array
     */
    protected function getOptions($source): array
    {
        if (null === $this->extrasResolver) {
            $extrasResolver = new OptionsResolver();

            $this->configureExtras($extrasResolver);

            $this->extrasResolver = $extrasResolver;
        }

        return [
            'label'  => $this->getLabel($source),
            'uri'    => $this->getUri($source),
            'extras' => $this->extrasResolver->resolve($this->getExtras($source)),
        ];
    }

    /**
     * @param mixed $source Source
     *
     * @return string|null
     */
    protected function getLabel($source): ?string
    {
        return null;
    }

    /**
     * @param mixed $source Source
     *
     * @return string|null
     */
    protected function getUri($source): ?string
    {
        return null;
    }

    /**
     * @param mixed $source Source
     *
     * @return array
     */
    protected function getExtras($source): array
    {
        return [];
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Extras resolver
     */
    protected function configureExtras(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'itemEntity'       => null,
                'objectName'       => null,
                'objectId'         => null,
                'object'           => null,
                'showSlugChildren' => false,
            ])
            ->setAllowedTypes('itemEntity', [Item::class, 'null'])
            ->setAllowedTypes('objectName', ['string', 'null'])
            ->setAllowedTypes('object', ['object', 'null'])
            ->setAllowedTypes('showSlugChildren', 'boolean');

        foreach ([
            'image',
            'hoverImage',
        ] as $extra) {
            $resolver
                ->setDefault($extra, null)
                ->setAllowedTypes($extra, [AbstractImage::class, 'null']);
        }
    }
}
