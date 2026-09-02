<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180611091358 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SALES_FORECAST_CREATE"
          AND user_group.name in ("ACL_AUTH_INTRANET")'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_ADMIN_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SALES_FORECAST_ADMIN_EDIT"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_GCEO",
            "ROLE_GCOO",
            "ROLE_GTD",
            "ROLE_CSD",
            "ROLE_EVP"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_RESTRICTED_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SALES_FORECAST_RESTRICTED_EDIT"
          AND user_group.name in (
            "ROLE_ASM",
            "ROLE_RCEO"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_FACTORY_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SALES_FORECAST_FACTORY_EDIT"
          AND user_group.name in (
            "ROLE_PSM",
            "ROLE_COO",
            "ROLE_RCEO"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
