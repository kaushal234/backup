<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180220152527 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tool ADD next_calibration_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', CHANGE tool_type_id tool_type_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE created_by created_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE serial_number serial_number VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE calibration_interval calibration_interval INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE calibration_notice calibration_notice INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE vendor_id vendor_id VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE vendor_erp vendor_erp VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE vendor_name vendor_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE deletedAt deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tool DROP next_calibration_date, CHANGE tool_type_id tool_type_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE created_by created_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE serial_number serial_number VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE calibration_interval calibration_interval INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE calibration_notice calibration_notice INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE vendor_id vendor_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE vendor_erp vendor_erp VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE vendor_name vendor_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE deletedAt deletedAt DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime)\'');
    }
}
