<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Twig\Extension;

use Darvin\MenuBundle\Breadcrumbs\BreadcrumbsMenuBuilderInterface;
use Knp\Menu\Twig\Helper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Breadcrumbs Twig extension
 */
class BreadcrumbsExtension extends AbstractExtension
{
    private const MENU_NAME = 'breadcrumbs';

    /**
     * @var \Darvin\MenuBundle\Breadcrumbs\BreadcrumbsMenuBuilderInterface
     */
    private $breadcrumbsMenuBuilder;

    /**
     * @var \Knp\Menu\Twig\Helper
     */
    private $helper;

    /**
     * @var array
     */
    private $defaultOptions;

    /**
     * @var string
     */
    private $defaultTemplate;

    /**
     * @param \Darvin\MenuBundle\Breadcrumbs\BreadcrumbsMenuBuilderInterface $breadcrumbsMenuBuilder Breadcrumbs menu builder
     * @param \Knp\Menu\Twig\Helper                                          $helper                 Helper
     * @param array                                                          $defaultOptions         Default options
     * @param string                                                         $defaultTemplate        Default template
     */
    public function __construct(
        BreadcrumbsMenuBuilderInterface $breadcrumbsMenuBuilder,
        Helper $helper,
        array $defaultOptions,
        string $defaultTemplate
    ) {
        $this->breadcrumbsMenuBuilder = $breadcrumbsMenuBuilder;
        $this->helper = $helper;
        $this->defaultOptions = $defaultOptions;
        $this->defaultTemplate = $defaultTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): iterable
    {
        yield new TwigFunction('darvin_menu_breadcrumbs', [$this, 'renderBreadcrumbs'], [
            'is_safe' => ['html'],
        ]);
    }

    /**
     * @param array       $options  Options
     * @param string|null $renderer Renderer
     *
     * @return string
     */
    public function renderBreadcrumbs(array $options = [], ?string $renderer = null): string
    {
        $options = array_merge($this->defaultOptions, $options);

        if (!isset($options['template'])) {
            $options['template'] = $this->defaultTemplate;
        }

        return $this->helper->render($this->breadcrumbsMenuBuilder->buildMenu(self::MENU_NAME), $options, $renderer);
    }
}
