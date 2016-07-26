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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Menu
 *
 * @ORM\Entity
 */
class Menu
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Id
     */
    private $id;

    /**
     * @var \Darvin\MenuBundle\Entity\Menu\Item[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Darvin\MenuBundle\Entity\Menu\Item", mappedBy="menu", cascade={"remove"})
     */
    private $items;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item[]|\Doctrine\Common\Collections\Collection $items items
     *
     * @return Menu
     */
    public function setItems(Collection $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]|\Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }
}
