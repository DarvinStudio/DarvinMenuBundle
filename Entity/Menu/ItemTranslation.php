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

use Darvin\ContentBundle\Traits\TranslationTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Menu item translation
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="menu_item_translation")
 */
class ItemTranslation
{
    use TranslationTrait;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $url;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled enabled
     *
     * @return ItemTranslation
     */
    public function setEnabled(?bool $enabled): ItemTranslation
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title title
     *
     * @return ItemTranslation
     */
    public function setTitle(?string $title): ItemTranslation
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url url
     *
     * @return ItemTranslation
     */
    public function setUrl(?string $url): ItemTranslation
    {
        $this->url = $url;

        return $this;
    }
}
