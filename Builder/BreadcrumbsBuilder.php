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
        $breadcrumbs = parent::buildMenu();
        $currentItems = $this->getCurrentItems($breadcrumbs);
        $breadcrumbs->setChildren([]);

        foreach ($currentItems as $item) {
            $item
                ->setChildren([])
                ->setParent(null);
            $breadcrumbs->addChild($item);
        }

        return $breadcrumbs;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('hidden_items', true);
    }

    /**
     * @param \Knp\Menu\ItemInterface $item Item
     *
     * @return \Knp\Menu\ItemInterface[]
     */
    private function getCurrentItems(ItemInterface $item)
    {
        $currentItems = [];

        foreach ($item->getChildren() as $child) {
            if ($this->matcher->isAncestor($child) || $this->matcher->isCurrent($child)) {
                $currentItems[] = $child;
            }

            $currentItems = array_merge($currentItems, $this->getCurrentItems($child));
        }

        return $currentItems;
    }
}
