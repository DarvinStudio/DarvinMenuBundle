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

use Knp\Menu\Renderer\RendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * JSON renderer Twig extension
 */
class JsonRendererExtension extends AbstractExtension
{
    /**
     * @var \Knp\Menu\Renderer\RendererInterface
     */
    private $renderer;

    /**
     * @param \Knp\Menu\Renderer\RendererInterface $renderer JSON renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('darvin_menu_json', [$this->renderer, 'render'], [
                'is_safe' => ['html'],
            ]),
        ];
    }
}
