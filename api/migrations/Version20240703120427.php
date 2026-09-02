<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20240703120427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add member of accountant team to edit manufacturing margin';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MANUFACTURING_MARGIN_EDIT_FACTORY")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MANUFACTURING_MARGIN_EDIT_FACTORY"
          AND user_group.name in ("ROLE_FC")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
