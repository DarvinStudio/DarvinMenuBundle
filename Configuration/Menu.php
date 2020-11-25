<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
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
    private $alias;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string|null
     */
    private $builderId;

    /**
     * @var string|null
     */
    private $builderAlias;

    /**
     * @param string      $alias        Alias
     * @param string|null $title        Title
     * @param string|null $builderId    Builder ID
     * @param string|null $builderAlias Builder alias
     */
    public function __construct(string $alias, ?string $title = null, ?string $builderId = null, ?string $builderAlias = null)
    {
        if (null === $title) {
            $title = sprintf('menu.%s', $alias);
        }

        $this->alias = $alias;
        $this->title = $title;
        $this->builderId = $builderId;
        $this->builderAlias = $builderAlias;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getBuilderId(): ?string
    {
        return $this->builderId;
    }

    /**
     * @return string|null
     */
    public function getBuilderAlias(): ?string
    {
        return $this->builderAlias;
    }
}
