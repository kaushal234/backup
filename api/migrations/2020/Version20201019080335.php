<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201019080335 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_ACCOUNT_RECEIVABLES_WRITE"
          AND user_group.name IN ("ROLE_RCEO")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_INVOICE_RECORD_WRITE_FULL"
          AND user_group.name IN ("ROLE_RCEO")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_INVOICE_RECORD_DUE_DATE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_INVOICE_RECORD_DUE_DATE_WRITE_SSO")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_INVOICE_RECORD_DUE_DATE_WRITE"
          AND user_group.name IN ("SUPERUSER", "ROLE_RCEO", "ROLE_CFO")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_INVOICE_RECORD_DUE_DATE_WRITE_SSO"
          AND user_group.name IN ("ROLE_AR", "ROLE_FC", "ROLE_SAM")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
