<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200806084709 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_ACCOUNT_RECEIVABLES_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_ACCOUNT_RECEIVABLES_VIEW_FULL")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_ACCOUNT_RECEIVABLES_VIEW_SSO")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_INVOICE_RECORD_WRITE_FULL")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_INVOICE_RECORD_WRITE_SSO")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_ACCOUNT_RECEIVABLES_WRITE"
          AND user_group.name IN ("SUPERUSER", "ROLE_CFO", "ROLE_FC", "ROLE_AR")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_ACCOUNT_RECEIVABLES_VIEW_FULL"
          AND user_group.name IN ("SUPERUSER", "GG_EXCOM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_ACCOUNT_RECEIVABLES_VIEW_SSO"
          AND user_group.name IN ("ROLE_SA", "ROLE_SAM", "ROLE_ASM", "ROLE_AR")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_INVOICE_RECORD_WRITE_FULL"
          AND user_group.name IN ("SUPERUSER", "ROLE_CFO", "ROLE_FC", "ROLE_EVP")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_INVOICE_RECORD_WRITE_SSO"
          AND user_group.name IN ("ROLE_SA", "ROLE_SAM", "ROLE_ASM", "ROLE_AR")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
