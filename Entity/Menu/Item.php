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
use Darvin\MenuBundle\Association\Associated;
use Darvin\Utils\Mapping\Annotation as Darvin;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Menu item
 *
 * @ORM\Entity(repositoryClass="Darvin\MenuBundle\Repository\Menu\ItemRepository")
 * @ORM\Table(name="menu_item")
 *
 * @method bool   isEnabled()
 * @method string getTitle()
 * @method string getUrl()
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
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $showChildren;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $associatedClass;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $associatedId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Gedmo\SortablePosition
     */
    private $position;

    /**
     * @var object
     *
     * @Darvin\CustomObject(classPropertyPath="associatedClass", initPropertyValuePath="associatedId")
     */
    private $associatedInstance;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->showChildren = false;
    }

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
     * @param \Darvin\MenuBundle\Association\Associated $associated Associated
     *
     * @return Item
     */
    public function setAssociated(Associated $associated)
    {
        $this->associatedClass = $associated->getClass();
        $this->associatedId = $associated->getId();

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Association\Associated
     */
    public function getAssociated()
    {
        return new Associated($this->associatedClass, $this->associatedId);
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
     * @param boolean $showChildren showChildren
     *
     * @return Item
     */
    public function setShowChildren($showChildren)
    {
        $this->showChildren = $showChildren;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowChildren()
    {
        return $this->showChildren;
    }

    /**
     * @param string $associatedClass associatedClass
     *
     * @return Item
     */
    public function setAssociatedClass($associatedClass)
    {
        $this->associatedClass = $associatedClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getAssociatedClass()
    {
        return $this->associatedClass;
    }

    /**
     * @param string $associatedId associatedId
     *
     * @return Item
     */
    public function setAssociatedId($associatedId)
    {
        $this->associatedId = $associatedId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAssociatedId()
    {
        return $this->associatedId;
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

    /**
     * @param object $associatedInstance associatedInstance
     *
     * @return Item
     */
    public function setAssociatedInstance($associatedInstance)
    {
        $this->associatedInstance = $associatedInstance;

        return $this;
    }

    /**
     * @return object
     */
    public function getAssociatedInstance()
    {
        return $this->associatedInstance;
    }
}
