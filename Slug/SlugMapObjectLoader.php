<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Slug;

use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Darvin\ImageBundle\ORM\ImageJoinerInterface;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Slug map object loader
 */
class SlugMapObjectLoader implements SlugMapObjectLoaderInterface
{
    /**
     * @var \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    private $genericCustomObjectLoader;

    /**
     * @var \Darvin\ImageBundle\ORM\ImageJoinerInterface
     */
    private $imageJoiner;

    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationJoinerInterface
     */
    private $translationJoiner;

    /**
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface        $genericCustomObjectLoader Generic custom object loader
     * @param \Darvin\ImageBundle\ORM\ImageJoinerInterface                  $imageJoiner               Image joiner
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                  $localeProvider            Locale provider
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner         Translation joiner
     */
    public function __construct(
        CustomObjectLoaderInterface $genericCustomObjectLoader,
        ImageJoinerInterface $imageJoiner,
        LocaleProviderInterface $localeProvider,
        TranslationJoinerInterface $translationJoiner
    ) {
        $this->genericCustomObjectLoader = $genericCustomObjectLoader;
        $this->imageJoiner = $imageJoiner;
        $this->localeProvider = $localeProvider;
        $this->translationJoiner = $translationJoiner;
    }

    /**
     * {@inheritDoc}
     */
    public function loadObjects(array $items): void
    {
        if (empty($items)) {
            return;
        }

        $imageJoiner       = $this->imageJoiner;
        $locale            = $this->localeProvider->getCurrentLocale();
        $translationJoiner = $this->translationJoiner;

        $this->genericCustomObjectLoader->loadCustomObjects($items, function (QueryBuilder $qb) use ($locale, $imageJoiner, $translationJoiner) {
            $imageJoiner->joinImages($qb);

            if ($translationJoiner->isTranslatable($qb->getRootEntities()[0])) {
                $translationJoiner->joinTranslation($qb, true, $locale, null, true);
            }
        });
    }
}
