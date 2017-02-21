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
use Symfony\Bridge\Doctrine\Validator\Constraints as Doctrine;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Menu item
 *
 * @ORM\Entity(repositoryClass="Darvin\MenuBundle\Repository\Menu\ItemRepository")
 * @ORM\Table(name="menu_item")
 *
 * @Doctrine\UniqueEntity(fields={"menu", "associatedClass", "associatedId"}, service="darvin_menu.unique_menu_item")
 *
 * @Gedmo\Loggable(logEntryClass="Darvin\AdminBundle\Entity\LogEntry")
 *
 * @method void   setEnabled(\bool $enabled)
 * @method bool   isEnabled()
 * @method string getTitle()
 * @method string getUrl()
 */
class Item
{
    use TranslatableTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Id
     */
    private $id;

    /**
     * @var \Darvin\MenuBundle\Entity\Menu\MenuItemImage
     *
     * @ORM\OneToOne(targetEntity="Darvin\MenuBundle\Entity\Menu\MenuItemImage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    private $image;

    /**
     * @var \Darvin\MenuBundle\Entity\Menu\MenuItemImage
     *
     * @ORM\OneToOne(targetEntity="Darvin\MenuBundle\Entity\Menu\MenuItemImage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    private $hoverImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank
     *
     * @Gedmo\SortableGroup
     * @Gedmo\Versioned
     */
    private $menu;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     *
     * @Gedmo\Versioned
     */
    private $showChildren;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Gedmo\Versioned
     */
    private $associatedClass;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Gedmo\Versioned
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
        $title = $this->getTitle();

        if (!empty($title)) {
            return $title;
        }
        if (!empty($this->associatedInstance) && method_exists($this->associatedInstance, '__toString')) {
            $string = (string) $this->associatedInstance;

            if (!empty($string)) {
                return $string;
            }
        }

        return (string) $this->id;
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
    public function setAssociated(Associated $associated = null)
    {
        $this->associatedClass = !empty($associated) ? $associated->getClass() : null;
        $this->associatedId = !empty($associated) ? $associated->getId() : null;

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
     * @param \Darvin\MenuBundle\Entity\Menu\MenuItemImage $image image
     *
     * @return Item
     */
    public function setImage(MenuItemImage $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\MenuItemImage
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\MenuItemImage $hoverImage hoverImage
     *
     * @return Item
     */
    public function setHoverImage(MenuItemImage $hoverImage = null)
    {
        $this->hoverImage = $hoverImage;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\MenuItemImage
     */
    public function getHoverImage()
    {
        return $this->hoverImage;
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
