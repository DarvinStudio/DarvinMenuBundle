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
use Darvin\MenuBundle\Slug\SlugMapObjectLoaderInterface;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\ORM\EntityManager;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Breadcrumbs builder
 */
class BreadcrumbsBuilder implements BreadcrumbsBuilderInterface
{
    private const MENU_NAME = 'breadcrumbs';

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
     * @var \Darvin\MenuBundle\Slug\SlugMapObjectLoaderInterface
     */
    private $slugMapObjectLoader;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $slugParameterName;

    /**
     * @param \Doctrine\ORM\EntityManager                                   $em                  Entity manager
     * @param \Darvin\MenuBundle\Item\Factory\Pool\ItemFactoryPoolInterface $itemFactoryPool     Item factory pool
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                $metadataFactory     Extended metadata factory
     * @param \Symfony\Component\HttpFoundation\RequestStack                $requestStack        Request stack
     * @param \Darvin\MenuBundle\Slug\SlugMapObjectLoaderInterface          $slugMapObjectLoader Slug map object loader
     * @param \Symfony\Contracts\Translation\TranslatorInterface            $translator          Translator
     * @param string                                                        $slugParameterName   Slug route parameter name
     */
    public function __construct(
        EntityManager $em,
        ItemFactoryPoolInterface $itemFactoryPool,
        MetadataFactoryInterface $metadataFactory,
        RequestStack $requestStack,
        SlugMapObjectLoaderInterface $slugMapObjectLoader,
        TranslatorInterface $translator,
        string $slugParameterName
    ) {
        $this->em = $em;
        $this->itemFactoryPool = $itemFactoryPool;
        $this->metadataFactory = $metadataFactory;
        $this->requestStack = $requestStack;
        $this->slugMapObjectLoader = $slugMapObjectLoader;
        $this->translator = $translator;
        $this->slugParameterName = $slugParameterName;
    }

    /**
     * {@inheritDoc}
     */
    public function buildBreadcrumbs(array $crumbs = []): ItemInterface
    {
        $root = $this->itemFactoryPool->createItem(self::MENU_NAME);

        if (!empty($crumbs)) {
            $i      = 0;
            $parent = $root;

            foreach ($crumbs as $label => $uri) {
                $child = $this->itemFactoryPool->createItem(implode('-', [self::MENU_NAME, $i]));
                $child->setLabel($this->translator->trans($label));
                $child->setUri($uri);

                $parent->addChild($child);

                $i++;
                $parent = $child;
            }

            return $root;
        }

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
            return $a['separator_count'] <=> $b['separator_count'];
        });

        /** @var \Darvin\ContentBundle\Entity\SlugMapItem[] $slugMapItems */
        $slugMapItems   = array_column($parentSlugMapItems, 'object');
        $slugMapItems[] = $currentSlugMapItem;

        $this->slugMapObjectLoader->loadObjects($slugMapItems);

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
