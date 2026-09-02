<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241128104627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'enlarge size of column name of table feature, and fix name of a feature';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feature CHANGE name name VARCHAR(100) NOT NULL');
        $this->addSql("UPDATE feature SET name='FEATURE_POWERBI_FINANCE_PRODUCTION_LOGISTIC_COO_READ' WHERE name = 'FEATURE_POWERBI_FINANCE_PRODUCTION_LOGISTIC_COO_RE'");
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
        $this->addSql('ALTER TABLE feature CHANGE name name VARCHAR(50) NOT NULL');
    }
}
