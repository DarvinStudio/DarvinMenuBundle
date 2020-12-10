<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DataFixtures\ORM\Menu;

use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\MenuBundle\Entity\MenuEntry;
use Darvin\MenuBundle\Entity\MenuEntryImage;
use Darvin\MenuBundle\Entity\MenuEntryInterface;
use Darvin\MenuBundle\Entity\MenuEntryTranslation;
use Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface;
use Darvin\Utils\DataFixtures\ORM\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Menu entry data fixture
 */
class LoadMenuEntryData extends AbstractFixture
{
    /**
     * @var int
     */
    private $minCount;

    /**
     * @var int
     */
    private $maxCount;

    /**
     * @var int
     */
    private $maxLevel;

    /**
     * @var array
     */
    private $entries;

    /**
     * @param int $minCount Minimum count
     * @param int $maxCount Maximum count
     * @param int $maxLevel Maximum level
     */
    public function __construct(int $minCount, int $maxCount, int $maxLevel)
    {
        $this->minCount = $minCount;
        $this->maxCount = $maxCount;
        $this->maxLevel = $maxLevel;

        $this->entries = [];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getMenuProvider()->getMenuCollection() as $menu) {
            $this->entries[$menu->getName()] = [];

            $count = $this->getFaker()->numberBetween($this->minCount, $this->maxCount);

            for ($i = 0; $i < $count; $i++) {
                $entry = $this->createEntry($menu->getName());

                $manager->persist($entry);

                $this->entries[$menu->getName()][] = $entry;
            }
        }

        $manager->flush();
    }

    /**
     * @param string $menu Menu name
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntry
     */
    private function createEntry(string $menu): MenuEntry
    {
        /** @var \Darvin\MenuBundle\Entity\MenuEntry $entry */
        $entry = $this->instantiateEntity(MenuEntryInterface::class);
        $entry->setLevel(1);
        $entry->setMenu($menu);

        if ($this->getFaker()->boolean(70)) {
            $entry->setParent($this->getParentEntry($menu));

            if (null !== $entry->getParent()) {
                $entry->setLevel($entry->getLevel() + $entry->getParent()->getLevel());
            }
        }
        if (1 === $entry->getLevel()) {
            $entry->setShowChildren($this->getFaker()->boolean());
            $entry->setSlugMapItem($this->getRandomEntity(SlugMapItem::class));
        }
        if ($this->getFaker()->boolean(80)) {
            $entry->setImage($this->createImage(true));
        }
        foreach ($this->getFakerLocales() as $locale => $fakerLocale) {
            $entry->addTranslation($this->createTranslation($entry, $locale, $fakerLocale));
        }

        return $entry;
    }

    /**
     * @param bool $grayscale Whether to generate grayscale image
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntryImage
     */
    private function createImage(bool $grayscale = false): MenuEntryImage
    {
        /** @var \Darvin\MenuBundle\Entity\MenuEntryImage $image */
        $image = $this->instantiateEntity(MenuEntryImage::class);
        $image->setFile($this->generateImageFile(null, null, null, $grayscale ? 'fff' : null, $grayscale ? 'ccc' : null));

        return $image;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\MenuEntry $entry       Menu entry
     * @param string                              $locale      Locale
     * @param string                              $fakerLocale Faker locale
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntryTranslation
     */
    private function createTranslation(MenuEntry $entry, string $locale, string $fakerLocale): MenuEntryTranslation
    {
        /** @var \Darvin\MenuBundle\Entity\MenuEntryTranslation $translation */
        $translation = $this->instantiateTranslation(MenuEntryInterface::class);
        $faker       = $this->getFaker($fakerLocale);

        $translation->setLocale($locale);

        if (null === $entry->getSlugMapItem()) {
            $translation->setTitle($faker->sentence(3));

            if ($faker->boolean(80)) {
                $translation->setUrl($faker->url);
            }
        }

        return $translation;
    }

    /**
     * @param string $menu Menu name
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntry|null
     */
    private function getParentEntry(string $menu): ?MenuEntry
    {
        /** @var \Darvin\MenuBundle\Entity\MenuEntry[] $entries */
        $entries = $this->entries[$menu];

        shuffle($entries);

        foreach ($entries as $entry) {
            if ($entry->getLevel() < $this->maxLevel) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * @return \Darvin\MenuBundle\Provider\Registry\MenuProviderRegistryInterface
     */
    private function getMenuProvider(): MenuProviderRegistryInterface
    {
        return $this->container->get('darvin_menu.provider_registry');
    }
}
