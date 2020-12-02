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

use Darvin\ContentBundle\Traits\TranslationTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Menu entry translation
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="menu_entry_translation")
 */
class MenuEntryTranslation
{
    use TranslationTrait;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $title;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $url;

    /**
     * Menu entry translation constructor.
     */
    public function __construct()
    {
        $this->enabled = true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled enabled
     *
     * @return MenuEntryTranslation
     */
    public function setEnabled(bool $enabled): MenuEntryTranslation
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title title
     *
     * @return MenuEntryTranslation
     */
    public function setTitle(?string $title): MenuEntryTranslation
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url url
     *
     * @return MenuEntryTranslation
     */
    public function setUrl(?string $url): MenuEntryTranslation
    {
        $this->url = $url;

        return $this;
    }
}
