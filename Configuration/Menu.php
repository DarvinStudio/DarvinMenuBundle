<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Configuration;

/**
 * Menu
 */
class Menu
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $title;

    /**
     * @param string $label Label
     */
    public function __construct($label)
    {
        $this->label = $label;
        $this->title = 'menu.'.$label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
