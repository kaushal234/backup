<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200724095626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Generate alert table for WMS';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE wms_picking_alerts (id INT AUTO_INCREMENT NOT NULL, created_by INT NOT NULL, erp INT NOT NULL, warehouse VARCHAR(3) NOT NULL, location VARCHAR(8) NOT NULL, part_number VARCHAR(25) NOT NULL, operation INT DEFAULT NULL, work_order VARCHAR(8) DEFAULT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, comment VARCHAR(512) DEFAULT NULL, status VARCHAR(10) NOT NULL, type VARCHAR(25) NOT NULL, INDEX IDX_BDD16DF6DE12AB56 (created_by), INDEX IDX_BDD16DF6FC51BA91 (erp), INDEX IDX_BDD16DF67B00651C (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wms_picking_alerts ADD CONSTRAINT FK_BDD16DF6DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PICKING_ALERT_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                                SELECT user_group.id, feature.id
                                FROM user_group, feature
                                WHERE feature.name = "FEATURE_PICKING_ALERT_WRITE" AND user_group.name IN ("SUPERUSER", "GG_WAREHOUSE", "ROLE_WS")
        ');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE wms_picking_alerts');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_PICKING_ALERT_WRITE")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_PICKING_ALERT_WRITE"');
    }
}
