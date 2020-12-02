<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Entity;

use Darvin\ImageBundle\Entity\Image\AbstractImage;
use Doctrine\ORM\Mapping as ORM;

/**
 * Menu entry image
 *
 * @ORM\Entity
 */
class MenuEntryImage extends AbstractImage
{
    /**
     * {@inheritDoc}
     */
    public static function getUploadDir(): string
    {
        return 'menu';
    }
}
