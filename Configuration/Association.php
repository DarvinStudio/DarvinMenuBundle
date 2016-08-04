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
    private $itemFactory;

    /**
     * @var string
     */
    private $hideProperty;

    /**
     * @var string
     */
    private $formType;

    /**
     * @var string
     */
    private $title;

    /**
     * @param string $alias        Alias
     * @param string $class        Class
     * @param string $itemFactory  Item factory
     * @param string $hideProperty Hide property
     * @param string $formType     Form type
     */
    public function __construct($alias, $class, $itemFactory, $hideProperty, $formType)
    {
        $this->alias = $alias;
        $this->class = $class;
        $this->itemFactory = $itemFactory;
        $this->hideProperty = $hideProperty;
        $this->formType = $formType;

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
    public function getItemFactory()
    {
        return $this->itemFactory;
    }

    /**
     * @return string
     */
    public function getHideProperty()
    {
        return $this->hideProperty;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
