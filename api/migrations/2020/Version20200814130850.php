<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200814130850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE wms_picking_alerts CHANGE operation operation INT DEFAULT NULL, CHANGE work_order work_order INT DEFAULT NULL, CHANGE closed_at closed_at DATETIME DEFAULT NULL, CHANGE comment comment VARCHAR(512) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE wms_picking_alerts CHANGE operation operation INT DEFAULT NULL, CHANGE work_order work_order VARCHAR(8) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE closed_at closed_at DATETIME DEFAULT \'NULL\', CHANGE comment comment VARCHAR(512) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`');
    }
}
