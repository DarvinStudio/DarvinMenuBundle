<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Matcher;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Matcher as BaseMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Matcher
 */
class Matcher extends BaseMatcher
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function isAncestor(ItemInterface $item, $depth = null)
    {
        if (parent::isAncestor($item, $depth)) {
            return true;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return false;
        }

        $path = $request->getBaseUrl().$request->getPathInfo();

        $itemUri = $item->getUri();

        if (empty($itemUri)) {
            return false;
        }

        $itemUri = rtrim($item->getUri(), '/').'/';

        return 0 === strpos($path, $itemUri);
    }
}
