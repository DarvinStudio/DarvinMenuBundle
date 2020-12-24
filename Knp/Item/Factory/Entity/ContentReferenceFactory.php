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

use Darvin\ContentBundle\Entity\ContentReference;

/**
 * Content reference KNP menu item factory
 */
class ContentReferenceFactory extends AbstractEntityFactory
{
    /**
     * {@inheritDoc}
     */
    public function supports($source): bool
    {
        return $source instanceof ContentReference;
    }

    /**
     * {@inheritDoc}
     */
    protected function getLabel($source): ?string
    {
        /** @var \Darvin\ContentBundle\Entity\ContentReference $contentReference */
        $contentReference = $source;

        return (string)$contentReference->getObject();
    }

    /**
     * {@inheritDoc}
     */
    protected function getUri($source): ?string
    {
        return $this->contentReferenceRouter->generateUrl($source);
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtras($source): array
    {
        /** @var \Darvin\ContentBundle\Entity\ContentReference $contentReference */
        $contentReference = $source;

        return [
            'object'     => $contentReference->getObject(),
            'objectId'   => $contentReference->getObjectId(),
            'objectName' => $this->objectNamer->name($contentReference->getObjectClass()),
        ];
    }
}
