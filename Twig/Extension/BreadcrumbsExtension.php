<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Twig\Extension;

use Darvin\MenuBundle\Breadcrumbs\BreadcrumbsBuilderInterface;
use Knp\Menu\Twig\Helper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Breadcrumbs Twig extension
 */
class BreadcrumbsExtension extends AbstractExtension
{
    /**
     * @var \Darvin\MenuBundle\Breadcrumbs\BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    /**
     * @var \Knp\Menu\Twig\Helper
     */
    private $helper;

    /**
     * @var string
     */
    private $defaultTemplate;

    /**
     * @param \Darvin\MenuBundle\Breadcrumbs\BreadcrumbsBuilderInterface $breadcrumbsBuilder Breadcrumbs builder
     * @param \Knp\Menu\Twig\Helper                                      $helper             Helper
     * @param string                                                     $defaultTemplate    Default template
     */
    public function __construct(BreadcrumbsBuilderInterface $breadcrumbsBuilder, Helper $helper, string $defaultTemplate)
    {
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->helper = $helper;
        $this->defaultTemplate = $defaultTemplate;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('darvin_menu_breadcrumbs', [$this, 'renderBreadcrumbs'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @param string|null $fallback    Fallback
     * @param array|null  $firstCrumbs First breadcrumbs
     * @param array|null  $mainCrumbs  Main breadcrumbs
     * @param array|null  $lastCrumbs  Last breadcrumbs
     * @param array       $options     Options
     * @param string|null $renderer    Renderer
     *
     * @return string
     */
    public function renderBreadcrumbs(
        ?string $fallback = null,
        ?array $firstCrumbs = null,
        ?array $mainCrumbs = null,
        ?array $lastCrumbs = null,
        array $options = [],
        ?string $renderer = null
    ): string {
        if (!isset($options['template'])) {
            $options['template'] = $this->defaultTemplate;
        }

        return $this->helper->render(
            $this->breadcrumbsBuilder->buildBreadcrumbs($fallback, $firstCrumbs, $mainCrumbs, $lastCrumbs),
            $options,
            $renderer
        );
    }
}
