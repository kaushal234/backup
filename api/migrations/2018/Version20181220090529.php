<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181220090529 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CATALOG_TYPE_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CATALOG_FAMILY_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_CATALOG_TYPE_EDIT"
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
          WHERE feature.name = "FEATURE_CATALOG_FAMILY_EDIT"
          AND user_group.name in (
            "SUPERUSER",
            "GG_ADMIN",
            "ROLE_PSM",
            "ROLE_PSE",
            "ROLE_PSA"
          )'
        );
        $this->addSql('DELETE feature_group
            FROM feature_group, user_group, feature
            WHERE feature_group.feature_id = feature.id
              AND feature_group.group_id = user_group.id
              AND feature.name = "FEATURE_CATALOG_EDIT"
              AND user_group.name IN (
                "GG_ADMIN",
                "ROLE_PSM",
                "ROLE_PSE",
                "ROLE_PSA"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
