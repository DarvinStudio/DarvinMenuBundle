<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Controller;

use Knp\Menu\Twig\Helper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

/**
 * Menu controller
 */
class MenuController
{
    /**
     * @var \Knp\Menu\Twig\Helper
     */
    private $helper;

    /**
     * @param \Knp\Menu\Twig\Helper $helper Helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param string      $menu          Menu alias
     * @param array       $buildOptions  Build options
     * @param array       $renderOptions Render options
     * @param string|null $renderer      Renderer
     * @param int         $maxAge        Max age
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(
        string $menu,
        array $buildOptions = [],
        array $renderOptions = [],
        ?string $renderer = null,
        int $maxAge = 30 * 24 * 60 * 60
    ): Response {
        $response = new Response($this->helper->render($this->helper->get($menu, [], $buildOptions), $renderOptions, $renderer));
        $response->setPublic();
        $response->setMaxAge($maxAge);
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');

        return $response;
    }
}
