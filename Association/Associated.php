<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Association;

/**
 * Associated
 */
class Associated
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $id;

    /**
     * @param string $class Class
     * @param string $id    ID
     */
    public function __construct($class = null, $id = null)
    {
        $this->class = $class;
        $this->id = $id;
    }

    /**
     * @param string $class class
     *
     * @return Associated
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $id id
     *
     * @return Associated
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
