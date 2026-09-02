<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170127142147 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE out_of_tolerance_form (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) DEFAULT NULL, impactAnalysis LONGTEXT DEFAULT NULL, correctiveMeasures LONGTEXT DEFAULT NULL, deletedAt DATETIME DEFAULT NULL, analysisBy INT DEFAULT NULL, INDEX IDX_FD97C2AD70099733 (analysisBy), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE calibration_log (id INT AUTO_INCREMENT NOT NULL, tool_id INT DEFAULT NULL, out_of_tolerance_form_id INT DEFAULT NULL, startDate DATETIME NOT NULL, endDate DATETIME NOT NULL, deletedAt DATETIME DEFAULT NULL, INDEX IDX_F68D4798F7B22CC (tool_id), INDEX IDX_F68D4792046B8D5 (out_of_tolerance_form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool (id INT AUTO_INCREMENT NOT NULL, tool_type_id INT NOT NULL, created_by INT DEFAULT NULL, location_area_id INT NOT NULL, serial_number VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, calibration_interval INT DEFAULT NULL, calibration_notice INT DEFAULT NULL, purchasing_date DATETIME NOT NULL, vendor_id VARCHAR(255) DEFAULT NULL, vendor_erp VARCHAR(255) DEFAULT NULL, vendor_name VARCHAR(255) DEFAULT NULL, status VARCHAR(25) DEFAULT NULL, deletedAt DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_20F33ED1D948EE2 (serial_number), INDEX IDX_20F33ED1D12881D0 (tool_type_id), INDEX IDX_20F33ED1DE12AB56 (created_by), INDEX IDX_20F33ED1534A5338 (location_area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool_type (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT NOT NULL, deletedAt DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location_area (id INT AUTO_INCREMENT NOT NULL, factory_id INT DEFAULT NULL, supervisor_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, deletedAt DATETIME DEFAULT NULL, INDEX IDX_57908828C7AF27D2 (factory_id), INDEX IDX_5790882819E9AC5F (supervisor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE out_of_tolerance_form ADD CONSTRAINT FK_FD97C2AD70099733 FOREIGN KEY (analysisBy) REFERENCES user (id)');
        $this->addSql('ALTER TABLE calibration_log ADD CONSTRAINT FK_F68D4798F7B22CC FOREIGN KEY (tool_id) REFERENCES tool (id)');
        $this->addSql('ALTER TABLE calibration_log ADD CONSTRAINT FK_F68D4792046B8D5 FOREIGN KEY (out_of_tolerance_form_id) REFERENCES out_of_tolerance_form (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1D12881D0 FOREIGN KEY (tool_type_id) REFERENCES tool_type (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1534A5338 FOREIGN KEY (location_area_id) REFERENCES location_area (id)');
        $this->addSql('ALTER TABLE location_area ADD CONSTRAINT FK_57908828C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE location_area ADD CONSTRAINT FK_5790882819E9AC5F FOREIGN KEY (supervisor_id) REFERENCES user (id)');

        $this->addSql('ALTER TABLE out_of_tolerance_form CHANGE status status VARCHAR(255) NOT NULL');

        $this->addSql('ALTER TABLE out_of_tolerance_form ADD file VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE calibration_log CHANGE endDate endDate DATETIME DEFAULT NULL');

        $this->addSql('ALTER TABLE out_of_tolerance_form ADD files LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', DROP file');

        $this->addSql('ALTER TABLE out_of_tolerance_form ADD calibration_log_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE out_of_tolerance_form ADD CONSTRAINT FK_FD97C2AD9B5C7F91 FOREIGN KEY (calibration_log_id) REFERENCES calibration_log (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FD97C2AD9B5C7F91 ON out_of_tolerance_form (calibration_log_id)');
        $this->addSql('ALTER TABLE calibration_log DROP INDEX IDX_F68D4792046B8D5, ADD UNIQUE INDEX UNIQ_F68D4792046B8D5 (out_of_tolerance_form_id)');

        $this->addSql('ALTER TABLE out_of_tolerance_form DROP FOREIGN KEY FK_FD97C2AD9B5C7F91');
        $this->addSql('DROP INDEX UNIQ_FD97C2AD9B5C7F91 ON out_of_tolerance_form');
        $this->addSql('ALTER TABLE out_of_tolerance_form DROP calibration_log_id');

        $this->addSql('ALTER TABLE calibration_log ADD certificate_file_name VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE calibration_log ADD calibration_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE out_of_tolerance_form');
        $this->addSql('DROP TABLE calibration_log');
        $this->addSql('DROP TABLE tool');
        $this->addSql('DROP TABLE tool_type');
        $this->addSql('DROP TABLE location_area');
    }
}
