<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Breadcrumbs builder
 */
class BreadcrumbsBuilder extends Builder
{
    /**
     * @var \Knp\Menu\Matcher\MatcherInterface
     */
    private $matcher;

    /**
     * @param \Knp\Menu\Matcher\MatcherInterface $matcher Matcher
     */
    public function setMatcher(MatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * {@inheritdoc}
     */
    public function buildMenu(array $options = [])
    {
        $root = $this->genericItemFactory->createItem($this->menuAlias);

        $item = $this->getCurrentItem(parent::buildMenu());

        if (empty($item)) {
            return $root;
        }

        $items = [];

        while ($item) {
            $items[] = $item;
            $item = $item->getParent();
        }

        // Remove menu root
        array_pop($items);

        /** @var \Knp\Menu\ItemInterface $item */
        foreach (array_reverse($items) as $item) {
            $root->addChild($item->setChildren([])->setParent(null));
        }

        return $root;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'force_add_children' => true,
            'hidden_items'       => true,
        ]);
    }

    /**
     * @param \Knp\Menu\ItemInterface $item Item
     *
     * @return \Knp\Menu\ItemInterface
     */
    private function getCurrentItem(ItemInterface $item)
    {
        foreach ($item->getChildren() as $child) {
            if ($this->matcher->isCurrent($child)) {
                return $child;
            }

            $current = $this->getCurrentItem($child);

            if (!empty($current)) {
                return $current;
            }
        }

        return $this->getAncestorItem($item);
    }

    /**
     * @param \Knp\Menu\ItemInterface $item Item
     *
     * @return \Knp\Menu\ItemInterface
     */
    private function getAncestorItem(ItemInterface $item)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return null;
        }

        $requestUri = $request->getRequestUri();

        foreach ($item->getChildren() as $child) {
            $uri = $child->getUri();

            if (!empty($uri)) {
                $uri = preg_replace('/(.html|\/+)$/', '', $uri).'/';

                if (0 === strpos($requestUri, $uri) && substr_count($uri, '/') === substr_count($requestUri, '/')) {
                    return $child;
                }
            }

            $ancestor = $this->getAncestorItem($child);

            if (!empty($ancestor)) {
                return $ancestor;
            }
        }

        return null;
    }
}
