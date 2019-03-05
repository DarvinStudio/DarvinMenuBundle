<?php declare(strict_types=1);
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
 * @method ItemTranslation[]|Collection getTranslations()
 */
class Item
{
    use TranslatableTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue
     * @ORM\Id
     *
     * @Gedmo\TreePathSource
     */
    protected $id;

    /**
     * @var Item|null
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
     * @var \Darvin\ContentBundle\Entity\SlugMapItem|null
     *
     * @ORM\ManyToOne(targetEntity="Darvin\ContentBundle\Entity\SlugMapItem")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $slugMapItem;

    /**
     * @var \Darvin\MenuBundle\Entity\Menu\MenuItemImage|null
     *
     * @ORM\OneToOne(targetEntity="Darvin\MenuBundle\Entity\Menu\MenuItemImage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    protected $image;

    /**
     * @var \Darvin\MenuBundle\Entity\Menu\MenuItemImage|null
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
     * @ORM\Column
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
     * @ORM\Column(length=2550, nullable=true)
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
    public function __toString(): string
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
    public function getMenuTitle(): string
    {
        return sprintf('menu.%s', $this->menu);
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\Item|null
     */
    public function getParent(): ?Item
    {
        return $this->parent;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item|null $parent parent
     *
     * @return Item
     */
    public function setParent(?Item $parent): Item
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Item[]|\Doctrine\Common\Collections\Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param Item[]|\Doctrine\Common\Collections\Collection $children children
     *
     * @return Item
     */
    public function setChildren(Collection $children): Item
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return \Darvin\ContentBundle\Entity\SlugMapItem|null
     */
    public function getSlugMapItem(): ?SlugMapItem
    {
        return $this->slugMapItem;
    }

    /**
     * @param \Darvin\ContentBundle\Entity\SlugMapItem|null $slugMapItem slugMapItem
     *
     * @return Item
     */
    public function setSlugMapItem(?SlugMapItem $slugMapItem): Item
    {
        $this->slugMapItem = $slugMapItem;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\MenuItemImage|null
     */
    public function getImage(): ?MenuItemImage
    {
        return $this->image;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\MenuItemImage|null $image image
     *
     * @return Item
     */
    public function setImage(?MenuItemImage $image): Item
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\MenuItemImage|null
     */
    public function getHoverImage(): ?MenuItemImage
    {
        return $this->hoverImage;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\MenuItemImage|null $hoverImage hoverImage
     *
     * @return Item
     */
    public function setHoverImage(?MenuItemImage $hoverImage): Item
    {
        $this->hoverImage = $hoverImage;

        return $this;
    }

    /**
     * @return string
     */
    public function getMenu(): ?string
    {
        return $this->menu;
    }

    /**
     * @param string $menu menu
     *
     * @return Item
     */
    public function setMenu(?string $menu): Item
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowChildren(): ?bool
    {
        return $this->showChildren;
    }

    /**
     * @param bool $showChildren showChildren
     *
     * @return Item
     */
    public function setShowChildren(?bool $showChildren): Item
    {
        $this->showChildren = $showChildren;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int $position position
     *
     * @return Item
     */
    public function setPosition(?int $position): Item
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return string
     */
    public function getTreePath(): ?string
    {
        return $this->treePath;
    }

    /**
     * @param string $treePath treePath
     *
     * @return Item
     */
    public function setTreePath(?string $treePath): Item
    {
        $this->treePath = $treePath;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param int $level level
     *
     * @return Item
     */
    public function setLevel(?int $level): Item
    {
        $this->level = $level;

        return $this;
    }
}
