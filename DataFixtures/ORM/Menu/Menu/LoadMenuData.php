<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DataFixtures\ORM\Menu\Menu;

use Darvin\MenuBundle\Entity\Menu\Menu;
use Darvin\MenuBundle\Entity\Menu\MenuTranslation;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Menu data fixture
 */
class LoadMenuData implements ContainerAwareInterface, FixtureInterface
{
    const COUNT = 3;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Faker\Generator[]
     */
    private $fakers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fakers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $fakerLocales = $this->getFakerLocales();

        for ($i = 0; $i < self::COUNT; $i++) {
            $manager->persist($this->createMenu($fakerLocales));
        }

        $manager->flush();
    }

    /**
     * @param array $fakerLocales Faker locales
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Menu
     */
    private function createMenu(array $fakerLocales)
    {
        $menu = new Menu();

        foreach ($fakerLocales as $locale => $fakerLocale) {
            $menu->addTranslation($this->createMenuTranslation($locale, $fakerLocale));
        }

        return $menu;
    }

    /**
     * @param string $locale      Locale
     * @param string $fakerLocale Faker locale
     *
     * @return \Darvin\MenuBundle\Entity\Menu\MenuTranslation|\Knp\DoctrineBehaviors\Model\Translatable\Translation
     */
    private function createMenuTranslation($locale, $fakerLocale)
    {
        $faker = $this->getFaker($fakerLocale);

        return (new MenuTranslation())
            ->setTitle($faker->realText(30))
            ->setLocale($locale);
    }

    /**
     * @param string $fakerLocale Faker locale
     *
     * @return \Faker\Generator
     */
    private function getFaker($fakerLocale = Factory::DEFAULT_LOCALE)
    {
        if (!isset($this->fakers[$fakerLocale])) {
            $this->fakers[$fakerLocale] = Factory::create($fakerLocale);
        }

        return $this->fakers[$fakerLocale];
    }

    /**
     * @return array
     */
    private function getFakerLocales()
    {
        $locales = [];

        foreach ($this->container->getParameter('locales') as $locale) {
            $locales[$locale] = $locale.'_'.strtoupper($locale);
        }

        return $locales;
    }
}
