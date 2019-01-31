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
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\Twig\Helper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Breadcrumbs Twig extension
 */
class BreadcrumbsExtension extends AbstractExtension
{
    /**
     * @var \Darvin\MenuBundle\Breadcrumbs\BreadcrumbsMenuBuilderInterface
     */
    private $breadcrumbsMenuBuilder;

    /**
     * @var \Knp\Menu\Provider\MenuProviderInterface
     */
    private $breadcrumbsMenuProvider;

    /**
     * @var \Knp\Menu\Twig\Helper
     */
    private $helper;

    /**
     * @var string
     */
    private $breadcrumbsMenuName;

    /**
     * @var array
     */
    private $defaultOptions;

    /**
     * @var string
     */
    private $defaultTemplate;

    /**
     * @param \Darvin\MenuBundle\Breadcrumbs\BreadcrumbsMenuBuilderInterface $breadcrumbsMenuBuilder  Breadcrumbs menu builder
     * @param \Knp\Menu\Provider\MenuProviderInterface                       $breadcrumbsMenuProvider Breadcrumbs menu provider
     * @param \Knp\Menu\Twig\Helper                                          $helper                  Helper
     * @param string                                                         $breadcrumbsMenuName     Breadcrumbs menu name
     * @param array                                                          $defaultOptions          Default options
     * @param string                                                         $defaultTemplate         Default template
     */
    public function __construct(
        BreadcrumbsMenuBuilderInterface $breadcrumbsMenuBuilder,
        MenuProviderInterface $breadcrumbsMenuProvider,
        Helper $helper,
        string $breadcrumbsMenuName,
        array $defaultOptions,
        string $defaultTemplate
    ) {
        $this->breadcrumbsMenuBuilder = $breadcrumbsMenuBuilder;
        $this->breadcrumbsMenuProvider = $breadcrumbsMenuProvider;
        $this->helper = $helper;
        $this->breadcrumbsMenuName = $breadcrumbsMenuName;
        $this->defaultOptions = $defaultOptions;
        $this->defaultTemplate = $defaultTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('darvin_menu_breadcrumbs', [$this, 'renderBreadcrumbs'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('darvin_menu_slug_breadcrumbs', [$this, 'renderSlugBreadcrumbs'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @param string|null $menuAlias Menu alias
     * @param array       $options   Options
     * @param string|null $renderer  Renderer
     *
     * @return string
     */
    public function renderBreadcrumbs(?string $menuAlias = null, array $options = [], ?string $renderer = null): string
    {
        $options = array_merge($this->defaultOptions, $options);

        if (!isset($options['template'])) {
            $options['template'] = $this->defaultTemplate;
        }

        return $this->helper->render(
            $this->breadcrumbsMenuProvider->get($this->breadcrumbsMenuName, [
                'menu_alias' => $menuAlias,
            ]),
            $options,
            $renderer
        );
    }

    /**
     * @param array       $options  Options
     * @param string|null $renderer Renderer
     *
     * @return string
     */
    public function renderSlugBreadcrumbs(array $options = [], ?string $renderer = null): string
    {
        $options = array_merge($this->defaultOptions, $options);

        if (!isset($options['template'])) {
            $options['template'] = $this->defaultTemplate;
        }

        return $this->helper->render($this->breadcrumbsMenuBuilder->buildMenu('breadcrumbs'), $options, $renderer);
    }
}
