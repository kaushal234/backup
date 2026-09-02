<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211028145019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create manuals tables (manual, manual_document, manual_document_file and manual_part_number) and feature associate';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MANUAL_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_MANUAL_ADMIN"
                          AND user_group.name IN ("SUPERUSER", "GG_ADMIN", "ROLE_ENG", "GG_SUPPORT", "MANUALS", "MANUALS_DIAG")');
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manual_document_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manual_document_files (id INT NOT NULL, document_id INT DEFAULT NULL, INDEX IDX_1CE23BEEC33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manual_documents (id INT AUTO_INCREMENT NOT NULL, manual_id INT DEFAULT NULL, category_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, position INT NOT NULL, factory_number VARCHAR(50) DEFAULT NULL, revision VARCHAR(11) DEFAULT NULL, type VARCHAR(30) NOT NULL, english_description LONGTEXT DEFAULT NULL, french_description LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_227790C59BA073D6 (manual_id), INDEX IDX_227790C512469DE2 (category_id), INDEX IDX_227790C5DE12AB56 (created_by), INDEX IDX_227790C516FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manual_parts (id INT AUTO_INCREMENT NOT NULL, document_id INT DEFAULT NULL, position INT NOT NULL, part_number VARCHAR(20) NOT NULL, quantity DOUBLE PRECISION DEFAULT NULL, unit_of_measure VARCHAR(10) DEFAULT NULL, english_description LONGTEXT DEFAULT NULL, french_description LONGTEXT DEFAULT NULL, preventive TINYINT(1) NOT NULL, maintenance TINYINT(1) NOT NULL, overhaul TINYINT(1) NOT NULL, critical TINYINT(1) NOT NULL, legacy_id INT NOT NULL, INDEX IDX_2B853C5DC33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manuals (id INT AUTO_INCREMENT NOT NULL, equipment_record_id INT DEFAULT NULL, equipment_serial_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, features LONGTEXT DEFAULT NULL, language VARCHAR(20) DEFAULT NULL, status VARCHAR(20) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_8717121C9FC03375 (equipment_record_id), UNIQUE INDEX UNIQ_8717121C7DBF96EC (equipment_serial_id), INDEX IDX_8717121CDE12AB56 (created_by), INDEX IDX_8717121C16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE manual_document_files ADD CONSTRAINT FK_1CE23BEEC33F7837 FOREIGN KEY (document_id) REFERENCES manual_documents (id)');
        $this->addSql('ALTER TABLE manual_document_files ADD CONSTRAINT FK_1CE23BEEBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE manual_documents ADD CONSTRAINT FK_227790C59BA073D6 FOREIGN KEY (manual_id) REFERENCES manuals (id)');
        $this->addSql('ALTER TABLE manual_documents ADD CONSTRAINT FK_227790C512469DE2 FOREIGN KEY (category_id) REFERENCES manual_document_categories (id)');
        $this->addSql('ALTER TABLE manual_documents ADD CONSTRAINT FK_227790C5DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE manual_documents ADD CONSTRAINT FK_227790C516FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE manual_parts ADD CONSTRAINT FK_2B853C5DC33F7837 FOREIGN KEY (document_id) REFERENCES manual_documents (id)');
        $this->addSql('ALTER TABLE manuals ADD CONSTRAINT FK_8717121C9FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE manuals ADD CONSTRAINT FK_8717121C7DBF96EC FOREIGN KEY (equipment_serial_id) REFERENCES equipment_serials (id)');
        $this->addSql('ALTER TABLE manuals ADD CONSTRAINT FK_8717121CDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE manuals ADD CONSTRAINT FK_8717121C16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manual_documents DROP FOREIGN KEY FK_227790C512469DE2');
        $this->addSql('ALTER TABLE manual_document_files DROP FOREIGN KEY FK_1CE23BEEC33F7837');
        $this->addSql('ALTER TABLE manual_parts DROP FOREIGN KEY FK_2B853C5DC33F7837');
        $this->addSql('ALTER TABLE manual_documents DROP FOREIGN KEY FK_227790C59BA073D6');
        $this->addSql('DROP TABLE manual_document_categories');
        $this->addSql('DROP TABLE manual_document_files');
        $this->addSql('DROP TABLE manual_documents');
        $this->addSql('DROP TABLE manual_parts');
        $this->addSql('DROP TABLE manuals');
    }
}
