<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Entity;

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
 * @ORM\Entity(repositoryClass="Darvin\MenuBundle\Repository\MenuItemRepository")
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
 * @method \Darvin\MenuBundle\Entity\MenuItemTranslation[]|Collection getTranslations()
 */
class MenuItem
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
     * @var MenuItem|null
     *
     * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="children")
     *
     * @Gedmo\TreeParent
     * @Gedmo\SortableGroup
     */
    protected $parent;

    /**
     * @var MenuItem[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="parent", cascade={"remove"})
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
     * @var \Darvin\MenuBundle\Entity\MenuItemImage|null
     *
     * @ORM\OneToOne(targetEntity="Darvin\MenuBundle\Entity\MenuItemImage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    protected $image;

    /**
     * @var \Darvin\MenuBundle\Entity\MenuItemImage|null
     *
     * @ORM\OneToOne(targetEntity="Darvin\MenuBundle\Entity\MenuItemImage", cascade={"persist", "remove"})
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
        $prefix = str_repeat('— ', $this->level == 0 ? $this->level : $this->level - 1);
        $title  = $this->getTitle();
        $url    = $this->getUrl();

        if (null !== $title) {
            return $prefix.$title;
        }
        if (null !== $this->slugMapItem && null !== $this->slugMapItem->getObject()) {
            return $prefix.$this->slugMapItem->getObject();
        }
        if (null !== $url) {
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
     * @return \Darvin\MenuBundle\Entity\MenuItem|null
     */
    public function getParent(): ?MenuItem
    {
        return $this->parent;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\MenuItem|null $parent parent
     *
     * @return MenuItem
     */
    public function setParent(?MenuItem $parent): MenuItem
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return MenuItem[]|\Doctrine\Common\Collections\Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param MenuItem[]|\Doctrine\Common\Collections\Collection $children children
     *
     * @return MenuItem
     */
    public function setChildren(Collection $children): MenuItem
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
     * @return MenuItem
     */
    public function setSlugMapItem(?SlugMapItem $slugMapItem): MenuItem
    {
        $this->slugMapItem = $slugMapItem;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\MenuItemImage|null
     */
    public function getImage(): ?MenuItemImage
    {
        return $this->image;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\MenuItemImage|null $image image
     *
     * @return MenuItem
     */
    public function setImage(?MenuItemImage $image): MenuItem
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\MenuItemImage|null
     */
    public function getHoverImage(): ?MenuItemImage
    {
        return $this->hoverImage;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\MenuItemImage|null $hoverImage hoverImage
     *
     * @return MenuItem
     */
    public function setHoverImage(?MenuItemImage $hoverImage): MenuItem
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
     * @return MenuItem
     */
    public function setMenu(?string $menu): MenuItem
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
     * @return MenuItem
     */
    public function setShowChildren(?bool $showChildren): MenuItem
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
     * @return MenuItem
     */
    public function setPosition(?int $position): MenuItem
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
     * @return MenuItem
     */
    public function setTreePath(?string $treePath): MenuItem
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
     * @return MenuItem
     */
    public function setLevel(?int $level): MenuItem
    {
        $this->level = $level;

        return $this;
    }
}
