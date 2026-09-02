<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250108124227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add feature to access power bi reports for COO, TCEO, TCOO, GCH and CPO';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_FINANCE_PRODUCTION_LOGISTIC_COO_READ"
          AND user_group.name in (
            "ROLE_COO",
            "ROLE_RCEO",
            "ROLE_TCEO",
            "ROLE_TCOO",
            "ROLE_GCH",
            "ROLE_CPO"
          )'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_FINANCE_READ"
          AND user_group.name in (
            "ROLE_COO",
            "ROLE_RCEO",
            "ROLE_TCEO",
            "ROLE_TCOO",
            "ROLE_GCH",
            "ROLE_CPO"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
