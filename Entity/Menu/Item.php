<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Entity\Menu;

use Darvin\ContentBundle\Traits\TranslatableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Menu item
 *
 * @ORM\Entity
 * @ORM\Table(name="menu_item")
 *
 * @method bool   isEnabled()
 * @method string getTitle()
 */
class Item
{
    use TranslatableTrait;

    const ITEM_CLASS = __CLASS__;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank
     *
     * @Gedmo\SortableGroup
     */
    private $menu;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $objectClass;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $objectId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Gedmo\SortablePosition
     */
    private $position;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getTitle();
    }

    /**
     * @return string
     */
    public function getMenuTitle()
    {
        return 'menu.'.$this->menu;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $menu menu
     *
     * @return Item
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * @return string
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param string $objectClass objectClass
     *
     * @return Item
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * @param string $objectId objectId
     *
     * @return Item
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $position position
     *
     * @return Item
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
