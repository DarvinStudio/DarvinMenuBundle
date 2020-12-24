<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Content reference choice admin form type
 */
class ContentReferenceChoiceType extends AbstractType
{
    /**
     * @var array
     */
    private $entityConfig;

    /**
     * @param array $entityConfig Entity configuration
     */
    public function __construct(array $entityConfig)
    {
        $this->entityConfig = $entityConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $classBlacklist = [];

        foreach ($this->entityConfig as $class => $config) {
            if (!$config['admin']) {
                $classBlacklist[] = $class;
            }
        }

        $resolver->setDefault('class_blacklist', $classBlacklist);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): string
    {
        return \Darvin\ContentBundle\Form\Type\Admin\ContentReferenceChoiceType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_menu_admin_content_reference_choice';
    }
}
