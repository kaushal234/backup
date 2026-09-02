<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20240327111050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new feature to allow reopen ESR';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_EQUIPMENT_SHIPPING_RECORD_REOPEN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_EQUIPMENT_SHIPPING_RECORD_REOPEN"
          AND user_group.name ="SUPERUSER"'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
