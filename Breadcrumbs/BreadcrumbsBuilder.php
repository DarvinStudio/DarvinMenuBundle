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
use Darvin\ContentBundle\Entity\ContentReference;
use Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface;
use Darvin\ContentBundle\Repository\ContentReferenceRepository;
use Darvin\MenuBundle\Knp\Item\Factory\Registry\KnpItemFactoryRegistryInterface;
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
    private const SLUG_PARAM     = 'slug';

    /**
     * @var \Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface
     */
    private $contentReferenceObjectLoader;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\Homepage\HomepageRouterInterface
     */
    private $homepageRouter;

    /**
     * @var \Darvin\MenuBundle\Knp\Item\Factory\Registry\KnpItemFactoryRegistryInterface
     */
    private $knpItemFactoryRegistry;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Darvin\ContentBundle\Reference\ContentReferenceObjectLoaderInterface        $contentReferenceObjectLoader Content reference object loader
     * @param \Doctrine\ORM\EntityManager                                                  $em                           Entity manager
     * @param \Darvin\Utils\Homepage\HomepageRouterInterface                               $homepageRouter               Homepage router
     * @param \Darvin\MenuBundle\Knp\Item\Factory\Registry\KnpItemFactoryRegistryInterface $knpItemFactoryRegistry       KNP menu item factory registry
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                               $metadataFactory              Extended metadata factory
     * @param \Symfony\Component\HttpFoundation\RequestStack                               $requestStack                 Request stack
     * @param \Symfony\Contracts\Translation\TranslatorInterface                           $translator                   Translator
     */
    public function __construct(
        ContentReferenceObjectLoaderInterface $contentReferenceObjectLoader,
        EntityManager $em,
        HomepageRouterInterface $homepageRouter,
        KnpItemFactoryRegistryInterface $knpItemFactoryRegistry,
        MetadataFactoryInterface $metadataFactory,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->contentReferenceObjectLoader = $contentReferenceObjectLoader;
        $this->em = $em;
        $this->homepageRouter = $homepageRouter;
        $this->knpItemFactoryRegistry = $knpItemFactoryRegistry;
        $this->metadataFactory = $metadataFactory;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildBreadcrumbs(?string $fallback = null, ?array $firstCrumbs = null, ?array $mainCrumbs = null, ?array $lastCrumbs = null): ItemInterface
    {
        $root = $this->knpItemFactoryRegistry->createItem(self::MENU_NAME);

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

        if (!isset($routeParams[self::SLUG_PARAM]) || null === $routeParams[self::SLUG_PARAM]) {
            return $parent;
        }

        $slug = $routeParams[self::SLUG_PARAM];

        $currentContentReference = $this->getContentReferenceRepository()->findOneBy([
            'slug' => $slug,
        ]);

        if (null === $currentContentReference) {
            return $parent;
        }

        $parentContentReferences = [];

        foreach ($this->getContentReferenceRepository()->getParentsBySlug($slug) as $parentContentReference) {
            $meta = $this->metadataFactory->getExtendedMetadata($parentContentReference->getObjectClass());

            if (!isset($meta['slugs'][$parentContentReference->getProperty()]['separator'])) {
                continue;
            }

            $separator = $meta['slugs'][$parentContentReference->getProperty()]['separator'];

            if (0 !== strpos($slug, $parentContentReference->getSlug().$separator)) {
                continue;
            }

            $parentContentReferences[] = [
                'object'          => $parentContentReference,
                'separator_count' => substr_count($parentContentReference->getSlug(), $separator),
            ];
        }

        usort($parentContentReferences, function (array $a, array $b): int {
            return $a['separator_count'] <=> $b['separator_count'];
        });

        /** @var \Darvin\ContentBundle\Entity\ContentReference[] $contentReferences */
        $contentReferences   = array_column($parentContentReferences, 'object');
        $contentReferences[] = $currentContentReference;

        $this->contentReferenceObjectLoader->loadObjects($contentReferences);

        foreach ($contentReferences as $contentReference) {
            if (null === $contentReference->getObject()) {
                continue;
            }

            $child = $this->knpItemFactoryRegistry->createItem($contentReference);

            $object = $contentReference->getObject();

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

            $child = $this->knpItemFactoryRegistry->createItem(implode('-', [self::MENU_NAME, $nameSuffix, $i]));
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
     * @return \Darvin\ContentBundle\Repository\ContentReferenceRepository
     */
    private function getContentReferenceRepository(): ContentReferenceRepository
    {
        return $this->em->getRepository(ContentReference::class);
    }
}
