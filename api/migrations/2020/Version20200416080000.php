<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200416080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SUPPLIER_PAYMENT_REPORT_VIEW")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_SUPPLIER_PAYMENT_REPORT_VIEW"
			AND user_group.name IN ("SUPERUSER", "ROLE_CEO", "ROLE_COO", "ROLE_RCEO", "ROLE_RCOO", "ROLE_CFO", "ROLE_MLM", "ROLE_FC", "GG_ADMIN")'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SUPPLIER_PAYMENT_REPORT_VIEW")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_SUPPLIER_PAYMENT_REPORT_VIEW"');
    }
}
