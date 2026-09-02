<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260724092503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'reassign the unused FEATURE_POWERBI_AES_READ feature to the groups allowed to view the AES Productivity Power BI report';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE feature_group
          FROM feature_group
          INNER JOIN feature ON feature.id = feature_group.feature_id
          WHERE feature.name = "FEATURE_POWERBI_AES_READ"'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_AES_READ"
          AND user_group.name IN (
              "ROLE_COO",
              "GG_HR",
              "ROLE_CEO",
              "ROLE_GCH",
              "SUPERUSER")'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
