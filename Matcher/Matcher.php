<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Matcher;

use Darvin\Utils\Homepage\HomepageRouterInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Matcher as BaseMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Matcher
 */
class Matcher extends BaseMatcher
{
    /**
     * @var \Darvin\Utils\Homepage\HomepageRouterInterface
     */
    private $homepageRouter;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var string|null
     */
    private $homepageUrl = null;

    /**
     * @param \Darvin\Utils\Homepage\HomepageRouterInterface $homepageRouter Homepage router
     */
    public function setHomepageRouter(HomepageRouterInterface $homepageRouter): void
    {
        $this->homepageRouter = $homepageRouter;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
     */
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function isAncestor(ItemInterface $item, $depth = null): bool
    {
        if (parent::isAncestor($item, $depth)) {
            return true;
        }

        $url = $item->getUri();

        if (null === $url || $url === $this->getHomepageUrl()) {
            return false;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return false;
        }

        return 0 === strpos($request->getBaseUrl().$request->getPathInfo(), rtrim($url, '/').'/');
    }

    /**
     * @return string|null
     */
    private function getHomepageUrl(): ?string
    {
        if (null === $this->homepageUrl) {
            $this->homepageUrl = $this->homepageRouter->generate();
        }

        return $this->homepageUrl;
    }
}
