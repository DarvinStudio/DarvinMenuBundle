<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Renderer\Json;

use Darvin\Utils\Json\JsonEncoderInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Renderer\RendererInterface;

/**
 * JSON renderer
 */
class JsonRenderer implements RendererInterface
{
    /**
     * @var \Darvin\MenuBundle\Renderer\Json\DataCollectorInterface
     */
    private $dataCollector;

    /**
     * @var \Darvin\Utils\Json\JsonEncoderInterface
     */
    private $encoder;

    /**
     * @param \Darvin\MenuBundle\Renderer\Json\DataCollectorInterface $dataCollector Data collector
     * @param \Darvin\Utils\Json\JsonEncoderInterface                 $encoder       JSON encoder
     */
    public function __construct(DataCollectorInterface $dataCollector, JsonEncoderInterface $encoder)
    {
        $this->dataCollector = $dataCollector;
        $this->encoder = $encoder;
    }

    /**
     * {@inheritDoc}
     */
    public function render(ItemInterface $item, array $options = []): string
    {
        $ids = $this->generateIds($item);

        return $this->encoder->encode($this->buildArray($item, $ids));
    }

    /**
     * @param \Knp\Menu\ItemInterface $item   Menu item
     * @param string                  $prefix ID prefix
     *
     * @return array
     */
    private function generateIds(ItemInterface $item, string $prefix = ''): array
    {
        $ids = [];
        $i   = 0;

        foreach ($item->getChildren() as $child) {
            $id = (string)++$i;

            if ('' !== $prefix) {
                $id = implode('.', [$prefix, $id]);
            }

            $ids[$child->getName()] = $id;

            if ($child->hasChildren()) {
                $ids = array_merge($ids, $this->generateIds($child, $id));
            }
        }

        return $ids;
    }

    /**
     * @param \Knp\Menu\ItemInterface $item Menu item
     * @param array                   $ids  IDs
     *
     * @return array
     */
    private function buildArray(ItemInterface $item, array $ids): array
    {
        $array = [];

        foreach ($item->getChildren() as $child) {
            if (!$child->isDisplayed()) {
                continue;
            }

            $array[] = array_filter($this->dataCollector->getData($child, $ids), function ($value): bool {
                return null !== $value;
            });

            if ($child->hasChildren()) {
                $array = array_merge($array, $this->buildArray($child, $ids));
            }
        }

        return $array;
    }
}
