<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180423145724 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CATALOG_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CATALOG_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CATALOG_DOWNLOAD")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_CATALOG_EDIT"
          AND user_group.name in (
            "SUPERUSER",
            "GG_ADMIN",
            "ROLE_PSM",
            "ROLE_PSE",
            "ROLE_PSA"
          )'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_CATALOG_CREATE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_PSM"
          )'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_CATALOG_DOWNLOAD"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_PSM",
            "ROLE_ASM"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
