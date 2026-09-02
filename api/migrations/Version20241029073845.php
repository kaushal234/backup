<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241029073845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add feature to access to CSR financial step';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CUSTOMER_SERVICE_RECORD_FINANCIAL_STEP")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_CUSTOMER_SERVICE_RECORD_FINANCIAL_STEP"
          AND user_group.name in ("SUPERUSER", "ROLE_CSM", "ROLE_CFO", "GG_ACCT")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
