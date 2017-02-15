<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\DataTransformer\Admin;

use Darvin\AdminBundle\EntityNamer\EntityNamerInterface;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Slug map item to array admin form data transformer
 */
class SlugMapItemToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var \Darvin\AdminBundle\EntityNamer\EntityNamerInterface
     */
    private $entityNamer;

    /**
     * @param \Darvin\AdminBundle\EntityNamer\EntityNamerInterface $entityNamer Entity namer
     */
    public function __construct(EntityNamerInterface $entityNamer)
    {
        $this->entityNamer = $entityNamer;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }
        if (!$value instanceof SlugMapItem) {
            throw new TransformationFailedException(sprintf('value must be instance of "%s".', SlugMapItem::class));
        }

        $classProperty = $this->entityNamer->name($value->getObjectClass()).'_'.$value->getProperty();

        return [
            'class_property' => $classProperty,
            $classProperty   => $value,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $value[$value['class_property']];
    }
}
