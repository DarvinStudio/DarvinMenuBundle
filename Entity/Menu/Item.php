<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Entity\Menu;

use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Traits\TranslatableTrait;
use Darvin\MenuBundle\Validation\Constraints as DarvinMenuAssert;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Menu item
 *
 * @ORM\Entity(repositoryClass="Darvin\MenuBundle\Repository\Menu\ItemRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="menu_item")
 *
 * @Gedmo\Tree(type="materializedPath")
 *
 * @DarvinMenuAssert\MenuItemValid
 *
 * @method string getTitle()
 * @method string getUrl()
 *
 * @method \Darvin\MenuBundle\Entity\Menu\ItemTranslation[]|\Doctrine\Common\Collections\Collection getTranslations()
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
     *
     * @Gedmo\TreePathSource
     */
    protected $id;

    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="children")
     *
     * @Gedmo\TreeParent
     * @Gedmo\SortableGroup
     */
    protected $parent;

    /**
     * @var Item[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Item", mappedBy="parent", cascade={"remove"})
     */
    protected $children;

    /**
     * @var \Darvin\ContentBundle\Entity\SlugMapItem
     *
     * @ORM\ManyToOne(targetEntity="Darvin\ContentBundle\Entity\SlugMapItem")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $slugMapItem;

    /**
     * @var \Darvin\MenuBundle\Entity\Menu\MenuItemImage
     *
     * @ORM\OneToOne(targetEntity="Darvin\MenuBundle\Entity\Menu\MenuItemImage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    protected $image;

    /**
     * @var \Darvin\MenuBundle\Entity\Menu\MenuItemImage
     *
     * @ORM\OneToOne(targetEntity="Darvin\MenuBundle\Entity\Menu\MenuItemImage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    protected $hoverImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank
     *
     * @Gedmo\SortableGroup
     */
    protected $menu;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $showChildren;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Gedmo\SortablePosition
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2550, nullable=true)
     *
     * @Gedmo\TreePath(separator="/", appendId="false")
     */
    protected $treePath;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Gedmo\TreeLevel
     */
    protected $level;

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
        $prefix = str_repeat('.....', $this->level == 0 ? $this->level : $this->level - 1);
        $title  = $this->getTitle();
        $url    = $this->getUrl();

        if (!empty($title)) {
            return $prefix.$title;
        }
        if (!empty($this->slugMapItem) && null !== $this->slugMapItem->getObject()) {
            return $prefix.$this->slugMapItem->getObject();
        }
        if (!empty($url)) {
            return $url;
        }

        return $prefix.$this->id;
    }

    /**
     * @return string
     */
    public function getMenuTitle()
    {
        return sprintf('menu.%s', $this->menu);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $parent parent
     *
     * @return Item
     */
    public function setParent(Item $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\Item
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Item[]|\Doctrine\Common\Collections\Collection $children children
     *
     * @return Item
     */
    public function setChildren(Collection $children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return Item[]|\Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem slugMapItem
     *
     * @return Item
     */
    public function setSlugMapItem(SlugMapItem $slugMapItem = null)
    {
        $this->slugMapItem = $slugMapItem;

        return $this;
    }

    /**
     * @return \Darvin\ContentBundle\Entity\SlugMapItem
     */
    public function getSlugMapItem()
    {
        return $this->slugMapItem;
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
     * @param string $treePath treePath
     *
     * @return Item
     */
    public function setTreePath($treePath)
    {
        $this->treePath = $treePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getTreePath()
    {
        return $this->treePath;
    }

    /**
     * @param int $level level
     *
     * @return Item
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }
}
