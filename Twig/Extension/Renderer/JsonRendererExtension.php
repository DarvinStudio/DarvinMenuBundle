<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Twig\Extension\Renderer;

use Darvin\MenuBundle\Renderer\JsonRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * JSON renderer Twig extension
 */
class JsonRendererExtension extends AbstractExtension
{
    /**
     * @var \Darvin\MenuBundle\Renderer\JsonRendererInterface
     */
    private $renderer;

    /**
     * @param \Darvin\MenuBundle\Renderer\JsonRendererInterface $renderer JSON renderer
     */
    public function __construct(JsonRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('darvin_menu_json', [$this->renderer, 'renderJson'], [
                'is_safe' => ['html'],
            ]),
        ];
    }
}
