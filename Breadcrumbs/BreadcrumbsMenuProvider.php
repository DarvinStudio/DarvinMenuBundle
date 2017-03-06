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
use Darvin\MenuBundle\Item\RootItemFactory;
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
     * @var \Darvin\MenuBundle\Item\RootItemFactory
     */
    private $rootItemFactory;

    /**
     * @var string
     */
    private $menuName;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var \Knp\Menu\ItemInterface
     */
    private $currentMenu;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container       DI container
     * @param \Knp\Menu\Matcher\MatcherInterface                        $matcher         Matcher
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration        $menuConfig      Menu configuration
     * @param \Darvin\MenuBundle\Item\RootItemFactory                   $rootItemFactory Root item factory
     * @param string                                                    $menuName        Breadcrumbs menu name
     */
    public function __construct(
        ContainerInterface $container,
        MatcherInterface $matcher,
        MenuConfiguration $menuConfig,
        RootItemFactory $rootItemFactory,
        $menuName
    ) {
        $this->container = $container;
        $this->matcher = $matcher;
        $this->menuConfig = $menuConfig;
        $this->rootItemFactory = $rootItemFactory;
        $this->menuName = $menuName;

        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $this->optionsResolver = $optionsResolver;

        $this->currentMenu = null;
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
        if (!empty($options['menu_alias'])) {
            return $this->getGenericMenuProvider()->get($this->menuConfig->getMenu($options['menu_alias'])->getMenuServiceAlias());
        }
        if (empty($this->currentMenu)) {
            $genericMenuProvider = $this->getGenericMenuProvider();

            foreach ($this->menuConfig->getMenus() as $config) {
                if (!$config->isBreadcrumbsEnabled()) {
                    continue;
                }

                $menu = $genericMenuProvider->get($config->getMenuServiceAlias());

                if ($this->isMenuCurrent($menu)) {
                    $this->currentMenu = $menu;

                    break;
                }
            }
            if (empty($this->currentMenu)) {
                $this->currentMenu = $this->rootItemFactory->createItem($this->menuName);
            }
        }

        return $this->currentMenu;
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
