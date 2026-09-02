<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20241202141642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new feature to allow CPO to be admin of ranking';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SUPPLIER_RANKING_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SUPPLIER_RANKING_ADMIN"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_CPO"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
