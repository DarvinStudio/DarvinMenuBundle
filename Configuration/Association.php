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
 * Association
 */
class Association
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var array
     */
    private $routeParams;

    /**
     * @var string
     */
    private $title;

    /**
     * @param string $alias       Alias
     * @param string $class       Class
     * @param string $routeName   Route name
     * @param array  $routeParams Route parameters
     */
    public function __construct($alias, $class, $routeName, array $routeParams)
    {
        $this->alias = $alias;
        $this->class = $class;
        $this->routeName = $routeName;
        $this->routeParams = $routeParams;

        $this->title = 'menu_association.'.$alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return array
     */
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
