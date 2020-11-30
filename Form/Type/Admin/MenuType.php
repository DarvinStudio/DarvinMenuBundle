<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\MenuBundle\Configuration\MenuConfigurationInterface;
use Darvin\MenuBundle\Entity\Menu\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Menu admin form type
 */
class MenuType extends AbstractType
{
    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfigurationInterface
     */
    private $menuConfig;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Darvin\MenuBundle\Configuration\MenuConfigurationInterface $menuConfig      Menu configuration
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface  $metadataManager Metadata manager
     * @param \Symfony\Component\HttpFoundation\RequestStack              $requestStack    Request stack
     */
    public function __construct(
        MenuConfigurationInterface $menuConfig,
        AdminMetadataManagerInterface $metadataManager,
        RequestStack $requestStack
    ) {
        $this->menuConfig = $menuConfig;
        $this->metadataManager = $metadataManager;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $metadataManager = $this->metadataManager;
        $requestStack    = $this->requestStack;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($metadataManager, $requestStack) {
            $data = $event->getData();

            if (!empty($data)) {
                return;
            }

            $request = $requestStack->getCurrentRequest();

            if (null === $request) {
                return;
            }

            $filterData = $request->query->get($metadataManager->getMetadata(Item::class)->getFilterFormTypeName());

            if (isset($filterData['menu'])) {
                $event->setData($filterData['menu']);
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('choices', $this->buildChoices());
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_menu_admin_menu';
    }

    /**
     * @return array
     */
    private function buildChoices(): array
    {
        $choices = [];

        foreach ($this->menuConfig->getMenus() as $menu) {
            $choices[$menu->getTitle()] = $menu->getName();
        }

        return $choices;
    }
}
