<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Breadcrumbs;

use Darvin\ContentBundle\Disableable\DisableableInterface;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Repository\SlugMapItemRepository;
use Darvin\MenuBundle\Item\RootItemFactory;
use Darvin\MenuBundle\Item\SlugMapItemFactory;
use Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\ORM\EntityManager;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Breadcrumbs menu builder
 */
class BreadcrumbsMenuBuilder
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Darvin\MenuBundle\Item\RootItemFactory
     */
    private $rootItemFactory;

    /**
     * @var \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader
     */
    private $slugMapItemCustomObjectLoader;

    /**
     * @var \Darvin\MenuBundle\Item\SlugMapItemFactory
     */
    private $slugMapItemFactory;

    /**
     * @var string
     */
    private $slugParameterName;

    /**
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface $metadataFactory Extended metadata factory
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
     * @param \Darvin\MenuBundle\Item\RootItemFactory $rootItemFactory Root item factory
     * @param \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader $slugMapItemCustomObjectLoader Slug map item custom object loader
     * @param \Darvin\MenuBundle\Item\SlugMapItemFactory $slugMapItemFactory Item from slug map item entity factory
     * @param string $slugParameterName
     */
    public function __construct(
        EntityManager $em,
        MetadataFactoryInterface $metadataFactory,
        RequestStack $requestStack,
        RootItemFactory $rootItemFactory,
        SlugMapItemCustomObjectLoader $slugMapItemCustomObjectLoader,
        SlugMapItemFactory $slugMapItemFactory,
        string $slugParameterName
    ) {
        $this->em = $em;
        $this->metadataFactory = $metadataFactory;
        $this->requestStack = $requestStack;
        $this->rootItemFactory = $rootItemFactory;
        $this->slugMapItemCustomObjectLoader = $slugMapItemCustomObjectLoader;
        $this->slugMapItemFactory = $slugMapItemFactory;
        $this->slugParameterName = $slugParameterName;
    }

    /**
     * @param string $name Menu name
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu(string $name): ItemInterface
    {
        $root = $this->rootItemFactory->createItem($name);

        $request = $this->requestStack->getMasterRequest();

        if (empty($request)) {
            return $root;
        }

        $routeParams = $request->attributes->get('_route_params', []);
        if (!isset($routeParams[$this->slugParameterName]) || empty($routeParams[$this->slugParameterName])) {
            return $root;
        }

        $slug = $routeParams['slug'];

        $currentSlugMapItem = $this->getSlugMapItemRepository()->findOneBy([
            'slug' => $slug,
        ]);

        if (empty($currentSlugMapItem)) {
            return $root;
        }

        $parentSlugMapItems = [];

        foreach ($this->getSlugMapItemRepository()->getParentsBySlug($slug) as $parentSlugMapItem) {
            $meta = $this->metadataFactory->getExtendedMetadata($parentSlugMapItem->getObjectClass());

            if (!isset($meta['slugs'][$parentSlugMapItem->getProperty()]['separator'])) {
                continue;
            }

            $separator = $meta['slugs'][$parentSlugMapItem->getProperty()]['separator'];

            if (0 !== strpos($slug, $parentSlugMapItem->getSlug().$separator)) {
                continue;
            }

            $parentSlugMapItems[] = [
                'object'          => $parentSlugMapItem,
                'separator_count' => substr_count($parentSlugMapItem->getSlug(), $separator),
            ];
        }

        usort($parentSlugMapItems, function (array $a, array $b) {
            return $a['separator_count'] === $b['separator_count'] ? 0 : ($a['separator_count'] > $b['separator_count'] ? 1 : -1);
        });

        /** @var \Darvin\ContentBundle\Entity\SlugMapItem[] $slugMapItems */
        $slugMapItems   = array_column($parentSlugMapItems, 'object');
        $slugMapItems[] = $currentSlugMapItem;

        $this->slugMapItemCustomObjectLoader->loadCustomObjects($slugMapItems);

        $parent = $root;

        foreach ($slugMapItems as $slugMapItem) {
            if (null === $slugMapItem->getObject()) {
                continue;
            }

            $item = $this->slugMapItemFactory->createItem($slugMapItem);

            $object = $slugMapItem->getObject();

            if ($object instanceof DisableableInterface && !$object->isEnabled()) {
                $item->setUri(null);
            }

            $parent->addChild($item);
            $parent = $item;
        }

        return $root;
    }

    /**
     * @return \Darvin\ContentBundle\Repository\SlugMapItemRepository
     */
    private function getSlugMapItemRepository(): SlugMapItemRepository
    {
        return $this->em->getRepository(SlugMapItem::class);
    }
}
