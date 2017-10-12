<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Item;

/**
 * Menu item manager
 */
class MenuItemManager
{
    /**
     * @var array
     */
    private $scheduledForAdding;

    /**
     * @var array
     */
    private $scheduledForRemoval;

    /**
     * Menu item manager constructor.
     */
    public function __construct()
    {
        $this->scheduledForAdding = $this->scheduledForRemoval = [];
    }

    /**
     * @param string $menuAlias Menu alias
     * @param object $entity    Entity
     */
    public function scheduleForAdding($menuAlias, $entity)
    {
        if (!isset($this->scheduledForAdding[$menuAlias])) {
            $this->scheduledForAdding[$menuAlias] = [];
        }

        $this->scheduledForAdding[$menuAlias][] = $entity;
    }

    /**
     * @param string $menuAlias Menu alias
     * @param object $entity    Entity
     */
    public function scheduleForRemoval($menuAlias, $entity)
    {
        if (!isset($this->scheduledForRemoval[$menuAlias])) {
            $this->scheduledForRemoval[$menuAlias] = [];
        }

        $this->scheduledForRemoval[$menuAlias][] = $entity;
    }

    /**
     * @return array
     */
    public function getScheduledForAdding()
    {
        return $this->scheduledForAdding;
    }

    /**
     * @return array
     */
    public function getScheduledForRemoval()
    {
        return $this->scheduledForRemoval;
    }
}
