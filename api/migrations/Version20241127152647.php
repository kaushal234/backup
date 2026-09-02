<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241127152647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add Powerbi read features for existing reports';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWERBI_FINANCE_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_FINANCE_READ"
          AND user_group.name in (
            "ROLE_FC",
            "SUPERUSER",
            "ROLE_CFO"
          )'
        );
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWERBI_FINANCE_PRODUCTION_LOGISTIC_COO_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_FINANCE_PRODUCTION_LOGISTIC_COO_READ"
          AND user_group.name in (
            "ROLE_FC",
            "SUPERUSER",
            "ROLE_CFO",
            "ROLE_PLANNER",
            "ROLE_PM",
            "ROLE_PS",
            "ROLE_BYR",
            "ROLE_MLM",
            "ROLE_COO"
          )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
