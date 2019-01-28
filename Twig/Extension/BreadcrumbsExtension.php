<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Twig\Extension;

use Darvin\MenuBundle\Breadcrumbs\BreadcrumbsMenuBuilder;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\Twig\Helper;

/**
 * Breadcrumbs Twig extension
 */
class BreadcrumbsExtension extends \Twig_Extension
{
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
     * @var BreadcrumbsMenuBuilder
     */
    private $slugBreadCrumbsBuilder;

    /**
     * @param \Knp\Menu\Provider\MenuProviderInterface $breadcrumbsMenuProvider Breadcrumbs menu provider
     * @param \Knp\Menu\Twig\Helper $helper Helper
     * @param string $breadcrumbsMenuName Breadcrumbs menu name
     * @param array $defaultOptions Default options
     * @param string $defaultTemplate Default template
     * @param BreadcrumbsMenuBuilder $breadcrumbsMenuBuilder
     */
    public function __construct(
        MenuProviderInterface $breadcrumbsMenuProvider,
        Helper $helper,
        $breadcrumbsMenuName,
        array $defaultOptions,
        $defaultTemplate,
        BreadcrumbsMenuBuilder $breadcrumbsMenuBuilder
    ) {
        $this->breadcrumbsMenuProvider = $breadcrumbsMenuProvider;
        $this->helper = $helper;
        $this->breadcrumbsMenuName = $breadcrumbsMenuName;
        $this->defaultOptions = $defaultOptions;
        $this->defaultTemplate = $defaultTemplate;
        $this->slugBreadCrumbsBuilder = $breadcrumbsMenuBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('darvin_menu_breadcrumbs', [$this, 'renderBreadcrumbs'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('darvin_menu_slug_breadcrumbs', [$this, 'renderSlugBreadcrumbs'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @param string $menuAlias Menu alias
     * @param array  $options   Options
     * @param string $renderer  Renderer
     *
     * @return string
     */
    public function renderBreadcrumbs($menuAlias = null, array $options = [], $renderer = null)
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
     * @param array $options
     * @param null $renderer
     * @return string
     */
    public function renderSlugBreadcrumbs(array $options = [], $renderer = null)
    {
        $options = array_merge($this->defaultOptions, $options);

        if (!isset($options['template'])) {
            $options['template'] = $this->defaultTemplate;
        }

        return $this->helper->render($this->slugBreadCrumbsBuilder->buildMenu('breadcrumbs'), $options, $renderer);
    }
}
