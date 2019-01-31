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
use Darvin\MenuBundle\Item\Factory\Pool\ItemFactoryPoolInterface;
use Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\ORM\EntityManager;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Breadcrumbs menu builder
 */
class BreadcrumbsMenuBuilder implements BreadcrumbsMenuBuilderInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\MenuBundle\Item\Factory\Pool\ItemFactoryPoolInterface
     */
    private $itemFactoryPool;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader
     */
    private $slugMapItemCustomObjectLoader;

    /**
     * @var string
     */
    private $slugParameterName;

    /**
     * @param \Doctrine\ORM\EntityManager                                   $em                            Entity manager
     * @param \Darvin\MenuBundle\Item\Factory\Pool\ItemFactoryPoolInterface $itemFactoryPool               Item factory pool
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                $metadataFactory               Extended metadata factory
     * @param \Symfony\Component\HttpFoundation\RequestStack                $requestStack                  Request stack
     * @param \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader      $slugMapItemCustomObjectLoader Slug map item custom object loader
     * @param string                                                        $slugParameterName             Slug route parameter name
     */
    public function __construct(
        EntityManager $em,
        ItemFactoryPoolInterface $itemFactoryPool,
        MetadataFactoryInterface $metadataFactory,
        RequestStack $requestStack,
        SlugMapItemCustomObjectLoader $slugMapItemCustomObjectLoader,
        string $slugParameterName
    ) {
        $this->em = $em;
        $this->itemFactoryPool = $itemFactoryPool;
        $this->metadataFactory = $metadataFactory;
        $this->requestStack = $requestStack;
        $this->slugMapItemCustomObjectLoader = $slugMapItemCustomObjectLoader;
        $this->slugParameterName = $slugParameterName;
    }

    /**
     * {@inheritDoc}
     */
    public function buildMenu(string $name): ItemInterface
    {
        $root = $this->itemFactoryPool->createItem($name);

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

            $item = $this->itemFactoryPool->createItem($slugMapItem);

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
