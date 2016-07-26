<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\Security;

use Darvin\AdminBundle\Security\Configuration\AbstractSecurityConfiguration;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Entity\Menu\Menu;

/**
 * Admin security configuration
 */
class Configuration extends AbstractSecurityConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_menu_security';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSecurableObjectClasses()
    {
        return [
            'menu'      => Menu::MENU_CLASS,
            'menu_item' => Item::ITEM_CLASS,
        ];
    }
}
