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
     * @var string
     */
    private $defaultTemplate;

    /**
     * @param \Knp\Menu\Provider\MenuProviderInterface $breadcrumbsMenuProvider Breadcrumbs menu provider
     * @param \Knp\Menu\Twig\Helper                    $helper                  Helper
     * @param string                                   $breadcrumbsMenuName     Breadcrumbs menu name
     * @param string                                   $defaultTemplate         Default template
     */
    public function __construct(MenuProviderInterface $breadcrumbsMenuProvider, Helper $helper, $breadcrumbsMenuName, $defaultTemplate)
    {
        $this->breadcrumbsMenuProvider = $breadcrumbsMenuProvider;
        $this->helper = $helper;
        $this->breadcrumbsMenuName = $breadcrumbsMenuName;
        $this->defaultTemplate = $defaultTemplate;
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
        ];
    }

    /**
     * @param array  $options  Options
     * @param string $renderer Renderer
     *
     * @return string
     */
    public function renderBreadcrumbs(array $options = [], $renderer = null)
    {
        if (!isset($options['template'])) {
            $options['template'] = $this->defaultTemplate;
        }

        return $this->helper->render($this->breadcrumbsMenuProvider->get($this->breadcrumbsMenuName), $options, $renderer);
    }
}
