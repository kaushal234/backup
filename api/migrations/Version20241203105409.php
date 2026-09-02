<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241203105409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add feature to access power bi report for sales and finances';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWERBI_FINANCE_SALES_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_FINANCE_SALES_READ"
          AND user_group.name in (
            "ROLE_FC",
            "SUPERUSER",
            "ROLE_CFO",
            "ROLE_SA",
            "ROLE_SAM",
            "GG_SALES",
            "role_gceo",
            "ROLE_GCOO",
            "ROLE_RCEO"
          )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
