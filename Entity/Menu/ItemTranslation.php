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
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        foreach (get_object_vars($this) as $var => $value) {
            if (in_array($var, ['translatable', 'locale'])) {
                continue;
            }
            if (!empty($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param boolean $enabled enabled
     *
     * @return ItemTranslation
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param string $title title
     *
     * @return ItemTranslation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $url url
     *
     * @return ItemTranslation
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
