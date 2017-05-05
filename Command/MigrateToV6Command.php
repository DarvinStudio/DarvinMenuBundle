<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrate to version 6 command
 */
class MigrateToV6Command extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('darvin:menu:migrate:6');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getEntityManager()->getConnection();

        $menuItems = $conn->executeQuery('SELECT id, associated_class, associated_id FROM menu_item')->fetchAll();
        $slugMapItems = $conn->executeQuery('SELECT id, object_class, object_id FROM content_slug_map')->fetchAll();

        $conn->exec('ALTER TABLE menu_item ADD parent_id INT DEFAULT NULL, ADD slug_map_item_id INT DEFAULT NULL, ADD tree_path VARCHAR(2550) DEFAULT NULL, ADD level INT DEFAULT NULL, DROP associated_class, DROP associated_id;');
        $conn->exec('ALTER TABLE menu_item ADD CONSTRAINT FK_D754D550727ACA70 FOREIGN KEY (parent_id) REFERENCES menu_item (id);');
        $conn->exec('ALTER TABLE menu_item ADD CONSTRAINT FK_D754D55018BE7F04 FOREIGN KEY (slug_map_item_id) REFERENCES content_slug_map (id) ON DELETE SET NULL;');
        $conn->exec('CREATE INDEX IDX_D754D550727ACA70 ON menu_item (parent_id);');
        $conn->exec('CREATE INDEX IDX_D754D55018BE7F04 ON menu_item (slug_map_item_id);');

        $stmt = $conn->prepare('UPDATE menu_item SET slug_map_item_id = :slug_map_item_id, tree_path = CONCAT(id, "/"), level = 1 WHERE id = :id');

        foreach ($menuItems as $menuItem) {
            $stmt->bindValue(
                'slug_map_item_id',
                $this->getSlugMapItemId($slugMapItems, $menuItem['associated_class'], $menuItem['associated_id']),
                \PDO::PARAM_INT
            );
            $stmt->bindValue('id', $menuItem['id'], \PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /**
     * @param array[] $slugMapItems    Slug map items
     * @param string  $associatedClass Associated class
     * @param mixed   $associatedId    Associated ID
     *
     * @return array
     */
    private function getSlugMapItemId(array $slugMapItems, $associatedClass, $associatedId)
    {
        foreach ($slugMapItems as $slugMapItem) {
            if ($slugMapItem['object_id'] === $associatedId
                && ($slugMapItem['object_class'] === $associatedClass
                    || in_array($associatedClass, class_parents($slugMapItem['object_class']))
                    || in_array($slugMapItem['object_class'], class_parents($associatedClass))
                )
            ) {
                return $slugMapItem['id'];
            }
        }

        return null;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }
}
