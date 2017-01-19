<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Entity\Menu;

use Darvin\ImageBundle\Entity\Image\AbstractImage;
use Darvin\MenuBundle\Configuration\Configuration;
use Doctrine\ORM\Mapping as ORM;

/**
 * Menu item image
 *
 * @ORM\Entity
 */
class MenuItemImage extends AbstractImage
{
    /**
     * {@inheritdoc}
     */
    public function getSizeGroupName()
    {
        return Configuration::IMAGE_SIZE_GROUP_NAME;
    }
}
