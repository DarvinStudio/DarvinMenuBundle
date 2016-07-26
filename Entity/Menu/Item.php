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
 * @ORM\Entity
 * @ORM\Table(name="menu_item")
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
     * @var \Darvin\MenuBundle\Entity\Menu\Menu
     *
     * @ORM\ManyToOne(targetEntity="Darvin\MenuBundle\Entity\Menu\Menu", inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private $menu;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Menu $menu menu
     *
     * @return Item
     */
    public function setMenu(Menu $menu = null)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }
}
