<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200407145723 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can onlyonly be executed safely on \'mysql\'.');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_PRODUCT_STANDARD_ITEM_WRITE"
          AND user_group.name IN ("SUPERUSER", "ROLE_PLANNER", "ROLE_PM")');

        $this->addSql('RENAME TABLE product_part_number TO product_standard_item');
        $this->addSql('ALTER TABLE product_standard_item CHANGE part_number standard_item VARCHAR(30) ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
