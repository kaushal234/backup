<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180531094321 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_VIEW_ASM")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SALES_FORECAST_VIEW_ASM"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_ASM"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_VIEW_CUSTOMER")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SALES_FORECAST_VIEW_CUSTOMER"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_EVP",
            "ROLE_FC",
            "ROLE_SAM",
            "ROLE_CEO",
            "ROLE_CFO"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_VIEW_SSO")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SALES_FORECAST_VIEW_SSO"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_EVP",
            "ROLE_FC",
            "ROLE_SAM",
            "ROLE_CEO",
            "ROLE_CFO"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_VIEW_FACTORY")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SALES_FORECAST_VIEW_FACTORY"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_COO",
            "ROLE_PSM",
            "ROLE_CEO",
            "ROLE_CFO",
            "ROLE_EM",
            "ROLE_MLM",
            "ROLE_PM",
            "ROLE_PSA",
            "ROLE_PSE",
            "GG_ACCT"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SALES_FORECAST_VIEW_FULL")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SALES_FORECAST_VIEW_FULL"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_CHAIRMAN",
            "ROLE_GCH",
            "ROLE_GCEO",
            "ROLE_CSD"
          )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
