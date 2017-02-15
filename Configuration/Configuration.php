<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Configuration;

use Darvin\ConfigBundle\Configuration\AbstractConfiguration;
use Darvin\ConfigBundle\Parameter\ParameterModel;
use Darvin\ImageBundle\Configuration\ImageConfigurationInterface;
use Darvin\ImageBundle\Form\Type\SizeType;
use Darvin\ImageBundle\Size\Size;

/**
 * Configuration
 */
class Configuration extends AbstractConfiguration implements ImageConfigurationInterface
{
    const IMAGE_SIZE_GROUP_NAME = 'menu';

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return [
            new ParameterModel(
                'image_sizes',
                ParameterModel::TYPE_ARRAY,
                [
                    'menu_item_image'       => new Size('menu_item_image', 32, 32),
                    'menu_item_hover_image' => new Size('menu_item_hover_image', 32, 32),
                ],
                [
                    'form' => [
                        'options' => [
                            'entry_type'    => SizeType::class,
                            'entry_options' => [
                                'size_group' => $this->getImageSizeGroupName(),
                            ],
                        ],
                    ],
                ]
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getImageSizes()
    {
        return $this->__call(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function isImageSizesGlobal()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageSizeGroupName()
    {
        return self::IMAGE_SIZE_GROUP_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_menu';
    }
}
