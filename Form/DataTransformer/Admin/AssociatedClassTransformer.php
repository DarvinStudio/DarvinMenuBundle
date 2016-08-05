<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\DataTransformer\Admin;

use Darvin\MenuBundle\Configuration\AssociationConfiguration;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Associated class admin data transformer
 */
class AssociatedClassTransformer implements DataTransformerInterface
{
    /**
     * @var \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private $associationConfig;

    /**
     * @param \Darvin\MenuBundle\Configuration\AssociationConfiguration $associationConfig Association configuration
     */
    public function __construct(AssociationConfiguration $associationConfig)
    {
        $this->associationConfig = $associationConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($alias)
    {
        return !empty($alias) ? $this->associationConfig->getAssociationByAlias($alias)->getClass() : null;
    }
}
