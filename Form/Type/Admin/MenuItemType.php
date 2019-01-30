<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Darvin\AdminBundle\Form\Type\EntityType;
use Darvin\MenuBundle\Repository\Menu\ItemRepository;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Menu item admin form type
 */
class MenuItemType extends AbstractType
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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $locale = $this->localeProvider->getCurrentLocale();

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($builder, $locale) {
            $data = $event->getData();

            if (empty($data['menu'])) {
                return;
            }

            $menu = $data['menu'];

            $parentField = $builder->create('parent', MenuItemParentType::class, [
                'auto_initialize' => false,
                'query_builder'   => function (ItemRepository $repository) use ($locale, $menu) {
                    return $repository->getAdminBuilder($locale, $menu);
                },
            ])->getForm();
            $event->getForm()->add($parentField);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return EntityType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_menu_admin_menu_item';
    }
}
