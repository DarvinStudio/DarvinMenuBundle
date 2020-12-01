<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Breadcrumbs;

use Darvin\ContentBundle\Disableable\DisableableInterface;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Repository\SlugMapItemRepository;
use Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface;
use Darvin\MenuBundle\Item\Factory\Pool\ItemFactoryPoolInterface;
use Darvin\Utils\Homepage\HomepageRouterInterface;
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
    private const HOMEPAGE_LABEL = 'breadcrumbs.homepage';
    private const MENU_NAME      = 'breadcrumbs';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\Homepage\HomepageRouterInterface
     */
    private $homepageRouter;

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
     * @var \Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface
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
     * @param \Darvin\Utils\Homepage\HomepageRouterInterface                $homepageRouter      Homepage router
     * @param \Darvin\MenuBundle\Item\Factory\Pool\ItemFactoryPoolInterface $itemFactoryPool     Item factory pool
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                $metadataFactory     Extended metadata factory
     * @param \Symfony\Component\HttpFoundation\RequestStack                $requestStack        Request stack
     * @param \Darvin\ContentBundle\Slug\SlugMapObjectLoaderInterface       $slugMapObjectLoader Slug map object loader
     * @param \Symfony\Contracts\Translation\TranslatorInterface            $translator          Translator
     * @param string                                                        $slugParameterName   Slug route parameter name
     */
    public function __construct(
        EntityManager $em,
        HomepageRouterInterface $homepageRouter,
        ItemFactoryPoolInterface $itemFactoryPool,
        MetadataFactoryInterface $metadataFactory,
        RequestStack $requestStack,
        SlugMapObjectLoaderInterface $slugMapObjectLoader,
        TranslatorInterface $translator,
        string $slugParameterName
    ) {
        $this->em = $em;
        $this->homepageRouter = $homepageRouter;
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
    public function buildBreadcrumbs(?string $fallback = null, ?array $firstCrumbs = null, ?array $mainCrumbs = null, ?array $lastCrumbs = null): ItemInterface
    {
        $root = $this->itemFactoryPool->createItem(self::MENU_NAME);

        $parent = $root;

        $homepageUrl = $this->homepageRouter->generate();

        if (null !== $homepageUrl) {
            $parent = $this->addScalars($parent, [self::HOMEPAGE_LABEL => $homepageUrl], 'homepage');
        }
        if (null !== $firstCrumbs) {
            $parent = $this->addScalars($root, $firstCrumbs, 'first');
        }

        $addFallback = false;

        if (null !== $mainCrumbs) {
            $parent = $this->addScalars($parent, $mainCrumbs, 'main');
        } else {
            $newParent = $this->addCurrent($parent);

            if ($newParent->getName() === $parent->getName()) {
                $addFallback = true;
            }

            $parent = $newParent;
        }
        if (null !== $lastCrumbs) {
            $parent = $this->addScalars($parent, $lastCrumbs, 'last');
        }
        if ($addFallback) {
            $this->addScalars($parent, [$fallback => null], 'fallback');
        }

        return $root;
    }

    /**
     * @param \Knp\Menu\ItemInterface $parent Parent item
     *
     * @return \Knp\Menu\ItemInterface
     */
    private function addCurrent(ItemInterface $parent): ItemInterface
    {
        $request = $this->requestStack->getMasterRequest();

        if (null === $request) {
            return $parent;
        }

        $routeParams = $request->attributes->get('_route_params', []);

        if (!isset($routeParams[$this->slugParameterName]) || null === $routeParams[$this->slugParameterName]) {
            return $parent;
        }

        $slug = $routeParams[$this->slugParameterName];

        $currentSlugMapItem = $this->getSlugMapItemRepository()->findOneBy([
            'slug' => $slug,
        ]);

        if (null === $currentSlugMapItem) {
            return $parent;
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

        foreach ($slugMapItems as $slugMapItem) {
            if (null === $slugMapItem->getObject()) {
                continue;
            }

            $child = $this->itemFactoryPool->createItem($slugMapItem);

            $object = $slugMapItem->getObject();

            if ($object instanceof DisableableInterface && !$object->isEnabled()) {
                $child->setUri(null);
            }

            $child->setUri($this->makeUrlAbsolute($child->getUri()));

            $parent->addChild($child);
            $parent = $child;
        }

        return $parent;
    }

    /**
     * @param \Knp\Menu\ItemInterface $parent     Parent item
     * @param iterable                $crumbs     Scalar breadcrumbs
     * @param string                  $nameSuffix Child item name suffix
     *
     * @return \Knp\Menu\ItemInterface
     */
    private function addScalars(ItemInterface $parent, iterable $crumbs, string $nameSuffix): ItemInterface
    {
        $i = 0;

        foreach ($crumbs as $label => $url) {
            $label = trim((string)$label);

            if ('' === $label) {
                continue;
            }

            $child = $this->itemFactoryPool->createItem(implode('-', [self::MENU_NAME, $nameSuffix, $i]));
            $child->setLabel($this->translator->trans($label));
            $child->setUri($this->makeUrlAbsolute($url));

            $parent->addChild($child);

            $i++;
            $parent = $child;
        }

        return $parent;
    }

    /**
     * @param string|null $url URL
     *
     * @return string|null
     */
    private function makeUrlAbsolute(?string $url): ?string
    {
        if (null === $url || null !== parse_url($url, PHP_URL_HOST)) {
            return $url;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return $url;
        }

        $url = ltrim($url, '/');
        $url = implode('/', [$request->getSchemeAndHttpHost(), $url]);

        return $url;
    }

    /**
     * @return \Darvin\ContentBundle\Repository\SlugMapItemRepository
     */
    private function getSlugMapItemRepository(): SlugMapItemRepository
    {
        return $this->em->getRepository(SlugMapItem::class);
    }
}
