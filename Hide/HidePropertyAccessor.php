<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Hide;

use Darvin\Utils\Service\ServiceProviderInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Hide property accessor
 */
class HidePropertyAccessor
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $associationConfigProvider;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $genericPropertyAccessor;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface              $associationConfigProvider Association configuration provider
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $genericPropertyAccessor   Generic property accessor
     */
    public function __construct(ServiceProviderInterface $associationConfigProvider, PropertyAccessorInterface $genericPropertyAccessor)
    {
        $this->associationConfigProvider = $associationConfigProvider;
        $this->genericPropertyAccessor = $genericPropertyAccessor;
    }

    /**
     * @param object $associated Associated
     * @param bool   $hidden     Is hidden
     */
    public function setHidden($associated, $hidden)
    {
        $this->genericPropertyAccessor->setValue($associated, $this->getHideProperty($associated), $hidden);
    }

    /**
     * @param object $associated Associated
     *
     * @return bool
     */
    public function isHidden($associated)
    {
        return $this->genericPropertyAccessor->getValue($associated, $this->getHideProperty($associated));
    }

    /**
     * @param object $associated Associated
     *
     * @return string
     */
    private function getHideProperty($associated)
    {
        return $this->getAssociationConfig()->getAssociationByClass(ClassUtils::getClass($associated))->getHideProperty();
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private function getAssociationConfig()
    {
        return $this->associationConfigProvider->getService();
    }
}
