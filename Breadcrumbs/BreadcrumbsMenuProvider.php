<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Breadcrumbs;

use Darvin\MenuBundle\Configuration\MenuConfiguration;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Breadcrumbs menu provider
 */
class BreadcrumbsMenuProvider implements MenuProviderInterface
{
    /**
     * @var \Darvin\MenuBundle\Breadcrumbs\BreadcrumbsMenuBuilder
     */
    private $breadcrumbsMenuBuilder;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Knp\Menu\Matcher\MatcherInterface
     */
    private $matcher;

    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private $menuConfig;

    /**
     * @var string
     */
    private $menuName;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var array
     */
    private $currentMenus;

    /**
     * @param \Darvin\MenuBundle\Breadcrumbs\BreadcrumbsMenuBuilder     $breadcrumbsMenuBuilder Breadcrumbs menu builder
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container              DI container
     * @param \Knp\Menu\Matcher\MatcherInterface                        $matcher                Matcher
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration        $menuConfig             Menu configuration
     * @param string                                                    $menuName               Breadcrumbs menu name
     */
    public function __construct(
        BreadcrumbsMenuBuilder $breadcrumbsMenuBuilder,
        ContainerInterface $container,
        MatcherInterface $matcher,
        MenuConfiguration $menuConfig,
        $menuName
    ) {
        $this->breadcrumbsMenuBuilder = $breadcrumbsMenuBuilder;
        $this->container = $container;
        $this->matcher = $matcher;
        $this->menuConfig = $menuConfig;
        $this->menuName = $menuName;

        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $this->optionsResolver = $optionsResolver;

        $this->currentMenus = [];
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, array $options = [])
    {
        $options = $this->optionsResolver->resolve($options);

        if (!$this->has($name, $options)) {
            throw new \InvalidArgumentException(sprintf('Menu "%s" does not exist.', $name));
        }

        return $this->getCurrentMenu($options);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name, array $options = [])
    {
        return $name === $this->menuName;
    }

    /**
     * @param array $options Options
     *
     * @return \Knp\Menu\ItemInterface
     */
    private function getCurrentMenu(array $options)
    {
        $alias = $options['menu_alias'];

        if (isset($this->currentMenus[$alias])) {
            return $this->currentMenus[$alias];
        }
        if (!empty($alias)) {
            $menu = $this->getGenericMenuProvider()->get($this->menuConfig->getMenu($alias)->getMenuServiceAlias());

            return $this->currentMenus[$alias] = $this->isMenuCurrent($menu)
                ? $menu
                : $this->breadcrumbsMenuBuilder->buildMenu($this->menuName);
        }

        $genericMenuProvider = $this->getGenericMenuProvider();

        foreach ($this->menuConfig->getMenus() as $config) {
            if (!$config->isBreadcrumbsEnabled()) {
                continue;
            }

            $menu = $genericMenuProvider->get($config->getMenuServiceAlias());

            if ($this->isMenuCurrent($menu)) {
                return $this->currentMenus[$alias] = $menu;
            }
        }

        return $this->currentMenus[$alias] = $this->breadcrumbsMenuBuilder->buildMenu($this->menuName);
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu Menu
     *
     * @return bool
     */
    private function isMenuCurrent(ItemInterface $menu)
    {
        foreach ($menu->getChildren() as $child) {
            if ($this->matcher->isAncestor($child) || $this->matcher->isCurrent($child)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Options resolver
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('menu_alias', null)
            ->setAllowedTypes('menu_alias', [
                'string',
                'null',
            ]);
    }

    /**
     * @return \Knp\Menu\Provider\MenuProviderInterface
     */
    private function getGenericMenuProvider()
    {
        return $this->container->get('knp_menu.menu_provider');
    }
}
