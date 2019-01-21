<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Admin\View;

use Darvin\AdminBundle\EntityNamer\EntityNamerInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\AbstractWidget;
use Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget;
use Darvin\MenuBundle\Entity\Menu\Item;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Slug map item link admin view widget
 */
class SlugMapItemLinkWidget extends AbstractWidget
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Darvin\AdminBundle\EntityNamer\EntityNamerInterface
     */
    private $entityNamer;

    /**
     * @var \Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget
     */
    private $showLinkWidget;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface        $adminRouter    Admin router
     * @param \Darvin\AdminBundle\EntityNamer\EntityNamerInterface  $entityNamer    Entity namer
     * @param \Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget $showLinkWidget Show link admin view widget
     * @param \Symfony\Contracts\Translation\TranslatorInterface    $translator     Translator
     */
    public function __construct(
        AdminRouterInterface $adminRouter,
        EntityNamerInterface $entityNamer,
        ShowLinkWidget $showLinkWidget,
        TranslatorInterface $translator
    ) {
        $this->adminRouter = $adminRouter;
        $this->entityNamer = $entityNamer;
        $this->showLinkWidget = $showLinkWidget;
        $this->translator = $translator;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     * @param array                               $options  Options
     *
     * @return string
     */
    protected function createContent($menuItem, array $options): ?string
    {
        if (null === $menuItem->getSlugMapItem()) {
            return null;
        }

        $entity = $menuItem->getSlugMapItem()->getObject();

        if (empty($entity)) {
            return null;
        }

        $content = $this->translator->trans(
            sprintf('slug_map_item.%s.%s', $this->entityNamer->name($entity), $menuItem->getSlugMapItem()->getProperty()),
            [],
            'admin'
        );

        $showLink = $this->showLinkWidget->getContent($entity, [
            'text' => true,
        ]);

        return $content.(!empty($showLink) ? $showLink : $entity);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedEntityClasses(): iterable
    {
        yield Item::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
