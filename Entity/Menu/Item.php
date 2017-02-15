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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Menu item
 *
 * @ORM\Entity(repositoryClass="Darvin\MenuBundle\Repository\Menu\ItemRepository")
 * @ORM\Table(name="menu_item")
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
     */
    private $menu;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $showChildren;

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
}
