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

use Darvin\AdminBundle\EntityNamer\EntityNamerInterface;
use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Metadata\SortCriteriaDetectorInterface;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Repository\SlugMapItemRepository;
use Darvin\MenuBundle\Form\DataTransformer\Admin\SlugMapItemToArrayTransformer;
use Darvin\MenuBundle\Slug\SlugMapObjectLoaderInterface;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Gedmo\Tree\TreeListener;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Slug map item admin form type
 */
class SlugMapItemType extends AbstractType
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\AdminBundle\EntityNamer\EntityNamerInterface
     */
    private $entityNamer;

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Darvin\MenuBundle\Slug\SlugMapObjectLoaderInterface
     */
    private $slugMapObjectLoader;

    /**
     * @var \Darvin\AdminBundle\Metadata\SortCriteriaDetectorInterface
     */
    private $sortCriteriaDetector;

    /**
     * @var \Gedmo\Tree\TreeListener
     */
    private $treeListener;

    /**
     * @var array
     */
    private $entityConfig;

    /**
     * @param \Psr\Container\ContainerInterface                           $container            DI container
     * @param \Doctrine\ORM\EntityManager                                 $em                   Entity manager
     * @param \Darvin\AdminBundle\EntityNamer\EntityNamerInterface        $entityNamer          Entity namer
     * @param \Darvin\Utils\ORM\EntityResolverInterface                   $entityResolver       Entity resolver
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface  $metadataManager      Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor     Property accessor
     * @param \Darvin\MenuBundle\Slug\SlugMapObjectLoaderInterface        $slugMapObjectLoader  Slug map object loader
     * @param \Darvin\AdminBundle\Metadata\SortCriteriaDetectorInterface  $sortCriteriaDetector Sort criteria detector
     * @param \Gedmo\Tree\TreeListener                                    $treeListener         Tree event listener
     * @param array                                                       $entityConfig         Entity configuration
     */
    public function __construct(
        ContainerInterface $container,
        EntityManager $em,
        EntityNamerInterface $entityNamer,
        EntityResolverInterface $entityResolver,
        AdminMetadataManagerInterface $metadataManager,
        PropertyAccessorInterface $propertyAccessor,
        SlugMapObjectLoaderInterface $slugMapObjectLoader,
        SortCriteriaDetectorInterface $sortCriteriaDetector,
        TreeListener $treeListener,
        array $entityConfig
    ) {
        $this->container = $container;
        $this->em = $em;
        $this->entityNamer = $entityNamer;
        $this->entityResolver = $entityResolver;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->slugMapObjectLoader = $slugMapObjectLoader;
        $this->sortCriteriaDetector = $sortCriteriaDetector;
        $this->treeListener = $treeListener;
        $this->entityConfig = $entityConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entityResolver      = $this->entityResolver;
        $propertiesByClasses = $this->getPropertiesByClasses();

        $classPropertyChoices = $this->buildClassPropertyChoices($propertiesByClasses);

        $builder->add('class_property', ChoiceType::class, [
            'label'    => 'menu_item.entity.slug_map_item',
            'choices'  => $classPropertyChoices,
            'required' => false,
            'attr'     => [
                'class' => 'class_property',
            ],
        ]);

        $classPropertyChoiceLabels = array_keys($classPropertyChoices);
        $classPropertyChoiceValues = array_values($classPropertyChoices);

        $i = 0;

        foreach ($propertiesByClasses as $class => $properties) {
            foreach ($properties as $property) {
                if (!isset($classPropertyChoiceValues[$i])) {
                    throw new \RuntimeException(<<<MESSAGE
Content slug map is invalid: please make sure you've replaced all overridden object classes in column "object_class" of
table "content_slug_map" in database.
MESSAGE
                    );
                }

                $builder->add($classPropertyChoiceValues[$i], EntityType::class, [
                    'label'         => $classPropertyChoiceLabels[$i],
                    'class'         => SlugMapItem::class,
                    'choice_label'  => 'id',
                    'required'      => false,
                    'query_builder' => function (SlugMapItemRepository $repository) use ($class, $entityResolver, $property) {
                        return $repository->createBuilderByClassesAndProperty([$class, $entityResolver->reverseResolve($class)], $property);
                    },
                    'attr' => [
                        'data-master'  => '.class_property',
                        'data-show-on' => $classPropertyChoiceValues[$i],
                    ],
                ]);

                $i++;
            }
        }

        $builder->addModelTransformer(new SlugMapItemToArrayTransformer($this->entityNamer));
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $slugMapItems = [];

        foreach ($view->children as $field) {
            if ('entity' !== $field->vars['block_prefixes'][2]) {
                continue;
            }
            /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
            foreach ($field->vars['choices'] as $choice) {
                $slugMapItems[] = $choice->data;
            }
        }

        $this->slugMapObjectLoader->loadObjects($slugMapItems);

        foreach ($view->children as $field) {
            if ('entity' !== $field->vars['block_prefixes'][2]) {
                continue;
            }

            /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView[] $choices */
            $choices = $entities = [];

            foreach ($field->vars['choices'] as $key => $choice) {
                /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem */
                $slugMapItem = $choice->data;

                if (null === $slugMapItem->getObject()) {
                    continue;
                }

                $choices[$key] = $choice;

                $entities[] = $slugMapItem->getObject();
            }
            if (empty($entities)) {
                continue;
            }

            $class = ClassUtils::getClass(reset($entities));

            $entities = $this->sortEntities($entities, $class);

            $treeConfig = $this->treeListener->getConfiguration($this->em, $class);
            $levelProperty = !empty($treeConfig) ? $treeConfig['level'] : null;

            $field->vars['choices'] = [];

            foreach ($entities as $entity) {
                foreach ($choices as $key => $choice) {
                    /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem */
                    $slugMapItem = $choice->data;

                    if ($slugMapItem->getObject() !== $entity) {
                        continue;
                    }

                    $choice->label = '';

                    if (!empty($levelProperty)) {
                        $choice->label .= str_repeat('.....', $this->propertyAccessor->getValue($entity, $levelProperty) - 1);
                    }

                    $choice->label .= (string)$entity;

                    $field->vars['choices'][$key] = $choice;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', false);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_menu_admin_slug_map_item';
    }

    /**
     * @param object[] $entities Entities
     * @param string   $class    Entity class
     *
     * @return object[]
     */
    private function sortEntities(array $entities, string $class): array
    {
        if (empty($entities) || !$this->metadataManager->hasMetadata($class)) {
            return $entities;
        }

        $sortCriteria = $this->sortCriteriaDetector->detectSortCriteria($class);

        if (!empty($sortCriteria)) {
            $propertyAccessor = $this->propertyAccessor;

            usort($entities, function ($entityA, $entityB) use ($propertyAccessor, $sortCriteria) {
                foreach ($sortCriteria as $property => $direction) {
                    $valueA = $propertyAccessor->getValue($entityA, $property);
                    $valueB = $propertyAccessor->getValue($entityB, $property);

                    if ($valueA === $valueB) {
                        continue;
                    }

                    $result = $valueA > $valueB ? 1 : -1;

                    if ('desc' === $direction) {
                        $result *= -1;
                    }

                    return $result;
                }

                return 0;
            });
        }

        $config = $this->metadataManager->getConfiguration($class);

        if (isset($config['sorter'])) {
            $sortCallback = [$this->container->get($config['sorter']['id']), $config['sorter']['method']];
            $entities = $sortCallback($entities);
        }

        return $entities;
    }

    /**
     * @param array $propertiesByClasses Slug map item properties by classes
     *
     * @return array
     */
    private function buildClassPropertyChoices(array $propertiesByClasses): array
    {
        $choices = [];

        foreach ($propertiesByClasses as $class => $properties) {
            foreach ($properties as $property) {
                $entityName = $this->entityNamer->name($class);
                $choices[sprintf('slug_map_item.%s.%s', $entityName, $property)] = $entityName.'_'.$property;
            }
        }

        return $choices;
    }

    /**
     * @return array
     */
    private function getPropertiesByClasses(): array
    {
        $classBlacklist = [];

        foreach ($this->entityConfig as $class => $config) {
            if (!$config['admin']) {
                $classBlacklist = array_merge($classBlacklist, [$class, $this->entityResolver->resolve($class)]);
            }
        }

        $classBlacklist = array_unique($classBlacklist);

        $qb = $this->getSlugMapItemRepository()->createQueryBuilder('o')
            ->select('o.objectClass')
            ->addSelect('o.property');

        if (!empty($classBlacklist)) {
            $qb
                ->andWhere($qb->expr()->notIn('o.objectClass', ':class_blacklist'))
                ->setParameter('class_blacklist', $classBlacklist);
        }

        $properties = [];

        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $class    = $this->entityResolver->resolve($row['objectClass']);
            $property = $row['property'];

            if (!isset($properties[$class])) {
                $properties[$class] = [];
            }
            if (!in_array($property, $properties[$class])) {
                $properties[$class][] = $property;
            }
        }

        return $properties;
    }

    /**
     * @return \Darvin\ContentBundle\Repository\SlugMapItemRepository
     */
    private function getSlugMapItemRepository(): SlugMapItemRepository
    {
        return $this->em->getRepository(SlugMapItem::class);
    }
}
