<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin\Entry;

use Darvin\MenuBundle\Repository\MenuEntryRepository;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Menu entry entity admin form type
 */
class EntityType extends AbstractType
{
    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @param \Darvin\Utils\Locale\LocaleProviderInterface $localeProvider Locale provider
     */
    public function __construct(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $locale = $this->localeProvider->getCurrentLocale();

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($builder, $locale): void {
            $data = $event->getData();

            if (null === $data['menu']) {
                return;
            }

            $menu = $data['menu'];

            $parentField = $builder->create('parent', ParentChoiceType::class, [
                'auto_initialize' => false,
                'query_builder'   => function (MenuEntryRepository $repository) use ($locale, $menu): QueryBuilder {
                    return $repository->createBuilderForAdminForm($menu, $locale);
                },
            ])->getForm();
            $event->getForm()->add($parentField);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): string
    {
        return \Darvin\AdminBundle\Form\Type\EntityType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_menu_admin_entry_entity';
    }
}
