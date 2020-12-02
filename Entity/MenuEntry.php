<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
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
 * Menu entry
 *
 * @ORM\Entity(repositoryClass="Darvin\MenuBundle\Repository\MenuEntryRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="menu_entry")
 *
 * @Gedmo\Tree(type="materializedPath")
 *
 * @DarvinMenuAssert\MenuEntryValid
 *
 * @method string|null getTitle()
 * @method string|null getUrl()
 *
 * @method \Darvin\MenuBundle\Entity\MenuEntryTranslation[]|Collection getTranslations()
 */
class MenuEntry implements MenuEntryInterface
{
    use TranslatableTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue
     * @ORM\Id
     *
     * @Gedmo\TreePathSource
     */
    protected $id;

    /**
     * @var MenuEntry|null
     *
     * @ORM\ManyToOne(targetEntity="Darvin\MenuBundle\Entity\MenuEntryInterface", inversedBy="children")
     *
     * @Gedmo\TreeParent
     * @Gedmo\SortableGroup
     */
    protected $parent;

    /**
     * @var MenuEntry[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Darvin\MenuBundle\Entity\MenuEntryInterface", mappedBy="parent", cascade={"remove"})
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
     * @var \Darvin\MenuBundle\Entity\MenuEntryImage|null
     *
     * @ORM\OneToOne(targetEntity="Darvin\MenuBundle\Entity\MenuEntryImage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    protected $image;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $showChildren;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     *
     * @Gedmo\SortableGroup
     */
    protected $menu;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     *
     * @Gedmo\SortablePosition
     */
    protected $position;

    /**
     * @var string|null
     *
     * @ORM\Column(length=2550, nullable=true)
     *
     * @Gedmo\TreePath(separator="/", appendId="false")
     */
    protected $treePath;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Gedmo\TreeLevel
     */
    protected $level;

    /**
     * Menu entry constructor.
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\MenuEntry|null
     */
    public function getParent(): ?MenuEntry
    {
        return $this->parent;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\MenuEntry|null $parent parent
     *
     * @return MenuEntry
     */
    public function setParent(?MenuEntry $parent): MenuEntry
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return MenuEntry[]|\Doctrine\Common\Collections\Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param MenuEntry[]|\Doctrine\Common\Collections\Collection $children children
     *
     * @return MenuEntry
     */
    public function setChildren(Collection $children): MenuEntry
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
     * @return MenuEntry
     */
    public function setSlugMapItem(?SlugMapItem $slugMapItem): MenuEntry
    {
        $this->slugMapItem = $slugMapItem;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\MenuEntryImage|null
     */
    public function getImage(): ?MenuEntryImage
    {
        return $this->image;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\MenuEntryImage|null $image image
     *
     * @return MenuEntry
     */
    public function setImage(?MenuEntryImage $image): MenuEntry
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowChildren(): bool
    {
        return $this->showChildren;
    }

    /**
     * @param bool $showChildren showChildren
     *
     * @return MenuEntry
     */
    public function setShowChildren(bool $showChildren): MenuEntry
    {
        $this->showChildren = $showChildren;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMenu(): ?string
    {
        return $this->menu;
    }

    /**
     * @param string|null $menu menu
     *
     * @return MenuEntry
     */
    public function setMenu(?string $menu): MenuEntry
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position position
     *
     * @return MenuEntry
     */
    public function setPosition(?int $position): MenuEntry
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTreePath(): ?string
    {
        return $this->treePath;
    }

    /**
     * @param string|null $treePath treePath
     *
     * @return MenuEntry
     */
    public function setTreePath(?string $treePath): MenuEntry
    {
        $this->treePath = $treePath;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param int|null $level level
     *
     * @return MenuEntry
     */
    public function setLevel(?int $level): MenuEntry
    {
        $this->level = $level;

        return $this;
    }
}
