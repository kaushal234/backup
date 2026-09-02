<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617120409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC Migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE service_activity (name VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, id INT AUTO_INCREMENT NOT NULL, UNIQUE INDEX UNIQ_550252675E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call (jira_tracteasy_issue_key VARCHAR(255) DEFAULT NULL, confidential TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, original_title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, original_description LONGTEXT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, solved_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deletedAt DATETIME DEFAULT NULL, serial_number VARCHAR(255) DEFAULT NULL, error_codes LONGTEXT DEFAULT NULL, indice_factor VARCHAR(255) NOT NULL, symptoms LONGTEXT DEFAULT NULL, original_symptoms LONGTEXT DEFAULT NULL, root_cause LONGTEXT DEFAULT NULL, original_root_cause LONGTEXT DEFAULT NULL, solution LONGTEXT DEFAULT NULL, original_solution LONGTEXT DEFAULT NULL, factory_flag TINYINT(1) NOT NULL, third_party_name VARCHAR(255) DEFAULT NULL, third_party_hours INT DEFAULT NULL, third_party_job_description LONGTEXT DEFAULT NULL, warranty_legacy_id INT DEFAULT NULL, token LONGTEXT DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, legacy_id INT NOT NULL, created_by_id INT DEFAULT NULL, equipment_record_id INT DEFAULT NULL, assignee_id INT DEFAULT NULL, technician_id INT DEFAULT NULL, unit_operational_status_id INT DEFAULT NULL, technician_on_call_type_id INT DEFAULT NULL, service_activity_id INT DEFAULT NULL, airport_id INT NOT NULL, sales_organisation_service_id INT NOT NULL, customer_id INT NOT NULL, main_contact_id INT DEFAULT NULL, INDEX IDX_3BD0B5C6B03A8386 (created_by_id), INDEX IDX_3BD0B5C69FC03375 (equipment_record_id), INDEX IDX_3BD0B5C659EC7D60 (assignee_id), INDEX IDX_3BD0B5C6E6C5D496 (technician_id), INDEX IDX_3BD0B5C64D735310 (unit_operational_status_id), INDEX IDX_3BD0B5C6BB4FD6E1 (technician_on_call_type_id), INDEX IDX_3BD0B5C64D5A463A (service_activity_id), INDEX IDX_3BD0B5C6289F53C8 (airport_id), INDEX IDX_3BD0B5C622F5EE4C (sales_organisation_service_id), INDEX IDX_3BD0B5C69395C3F3 (customer_id), INDEX IDX_3BD0B5C6DF595129 (main_contact_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_extranet_user (technician_on_call_id INT NOT NULL, extranet_user_id INT NOT NULL, INDEX IDX_2BEA67B1EC02A7D0 (technician_on_call_id), INDEX IDX_2BEA67B1D2CDD54B (extranet_user_id), PRIMARY KEY(technician_on_call_id, extranet_user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_backlog_report (date DATE NOT NULL, id INT AUTO_INCREMENT NOT NULL, sales_organisation_id INT NOT NULL, INDEX IDX_6BA995A0E8E5F9D1 (sales_organisation_id), UNIQUE INDEX unique_date_location (sales_organisation_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_backlog_association (backlog_id INT NOT NULL, technician_on_call_id INT NOT NULL, INDEX IDX_7995E6AFF1F06ABE (backlog_id), INDEX IDX_7995E6AFEC02A7D0 (technician_on_call_id), PRIMARY KEY(backlog_id, technician_on_call_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_files (technician_on_call_id INT DEFAULT NULL, id INT NOT NULL, INDEX IDX_9DF165E0EC02A7D0 (technician_on_call_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_main_files (technician_on_call_id INT DEFAULT NULL, id INT NOT NULL, INDEX IDX_2B41AB3BEC02A7D0 (technician_on_call_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_oldest_report (date DATE NOT NULL, days INT NOT NULL, id INT AUTO_INCREMENT NOT NULL, technician_on_call_id INT NOT NULL, sales_organisation_id INT NOT NULL, INDEX IDX_E5BBFCF9EC02A7D0 (technician_on_call_id), INDEX IDX_E5BBFCF9E8E5F9D1 (sales_organisation_id), UNIQUE INDEX unique_toc_oldest (technician_on_call_id, sales_organisation_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_part (part_number VARCHAR(255) DEFAULT NULL, vendor_part_number VARCHAR(255) DEFAULT NULL, description VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, defective TINYINT(1) NOT NULL, replacement VARCHAR(255) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, technician_on_call_id INT NOT NULL, INDEX IDX_E9E87906B03A8386 (created_by_id), INDEX IDX_E9E87906EC02A7D0 (technician_on_call_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_survey (execution INT NOT NULL, responsiveness INT NOT NULL, communication INT NOT NULL, attitude INT NOT NULL, comment LONGTEXT NOT NULL, created_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, technician_on_call_id INT NOT NULL, created_by_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1F7954F1EC02A7D0 (technician_on_call_id), INDEX IDX_1F7954F1B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_tags (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_tags_xref (technician_on_call_tag_id INT NOT NULL, technician_on_call_id INT NOT NULL, INDEX IDX_FDF1C1138F4AC1C (technician_on_call_tag_id), INDEX IDX_FDF1C113EC02A7D0 (technician_on_call_id), PRIMARY KEY(technician_on_call_tag_id, technician_on_call_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_type (name VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, id INT AUTO_INCREMENT NOT NULL, UNIQUE INDEX UNIQ_2C395EE95E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_zone (name VARCHAR(255) NOT NULL, delay INT NOT NULL, id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician_on_call_zone_country (zone_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_791F7C169F2C3FAB (zone_id), UNIQUE INDEX UNIQ_791F7C16F92F3E70 (country_id), PRIMARY KEY(zone_id, country_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C69FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C659EC7D60 FOREIGN KEY (assignee_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6E6C5D496 FOREIGN KEY (technician_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C64D735310 FOREIGN KEY (unit_operational_status_id) REFERENCES unit_operational_statuses (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6BB4FD6E1 FOREIGN KEY (technician_on_call_type_id) REFERENCES technician_on_call_type (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C64D5A463A FOREIGN KEY (service_activity_id) REFERENCES service_activity (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C622F5EE4C FOREIGN KEY (sales_organisation_service_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C69395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6DF595129 FOREIGN KEY (main_contact_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call_extranet_user ADD CONSTRAINT FK_2BEA67B1EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_extranet_user ADD CONSTRAINT FK_2BEA67B1D2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_backlog_report ADD CONSTRAINT FK_6BA995A0E8E5F9D1 FOREIGN KEY (sales_organisation_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE technician_on_call_backlog_association ADD CONSTRAINT FK_7995E6AFF1F06ABE FOREIGN KEY (backlog_id) REFERENCES technician_on_call_backlog_report (id)');
        $this->addSql('ALTER TABLE technician_on_call_backlog_association ADD CONSTRAINT FK_7995E6AFEC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_files ADD CONSTRAINT FK_9DF165E0EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_files ADD CONSTRAINT FK_9DF165E0BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_main_files ADD CONSTRAINT FK_2B41AB3BEC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_main_files ADD CONSTRAINT FK_2B41AB3BBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_oldest_report ADD CONSTRAINT FK_E5BBFCF9EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_oldest_report ADD CONSTRAINT FK_E5BBFCF9E8E5F9D1 FOREIGN KEY (sales_organisation_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE technician_on_call_part ADD CONSTRAINT FK_E9E87906B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call_part ADD CONSTRAINT FK_E9E87906EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_survey ADD CONSTRAINT FK_1F7954F1EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_survey ADD CONSTRAINT FK_1F7954F1B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call_tags ADD CONSTRAINT FK_CF5B9DE6BF396750 FOREIGN KEY (id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_tags_xref ADD CONSTRAINT FK_FDF1C1138F4AC1C FOREIGN KEY (technician_on_call_tag_id) REFERENCES technician_on_call_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_tags_xref ADD CONSTRAINT FK_FDF1C113EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_zone_country ADD CONSTRAINT FK_791F7C169F2C3FAB FOREIGN KEY (zone_id) REFERENCES technician_on_call_zone (id)');
        $this->addSql('ALTER TABLE technician_on_call_zone_country ADD CONSTRAINT FK_791F7C16F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE ai_logs CHANGE title title VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ai_responses CHANGE content content LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE customer_service_record ADD technician_on_call_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_service_record ADD CONSTRAINT FK_3C9EAE6DEC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('CREATE INDEX IDX_3C9EAE6DEC02A7D0 ON customer_service_record (technician_on_call_id)');
        $this->addSql('ALTER TABLE directory_position CHANGE mentor_mandatory mentor_mandatory TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE equipment_records CHANGE state state VARCHAR(50) DEFAULT \'ACTIVE\' NOT NULL');
        $this->addSql('ALTER TABLE modules CHANGE disabledForTroubleTicket disabledForTroubleTicket TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE non_conformity CHANGE location_id location_id INT NOT NULL');
        $this->addSql('ALTER TABLE spare_parts_requests_toc ADD technician_on_call_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE spare_parts_requests_toc ADD CONSTRAINT FK_5C3E7598EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('CREATE INDEX IDX_5C3E7598EC02A7D0 ON spare_parts_requests_toc (technician_on_call_id)');
        $this->addSql('ALTER TABLE toc_hour_meter_transactions ADD technician_on_call_id INT DEFAULT NULL, CHANGE toc_legacy_id toc_legacy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE toc_hour_meter_transactions ADD CONSTRAINT FK_59C11EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('CREATE INDEX IDX_59C11EC02A7D0 ON toc_hour_meter_transactions (technician_on_call_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6B03A8386');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C69FC03375');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C659EC7D60');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6E6C5D496');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C64D735310');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6BB4FD6E1');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C64D5A463A');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6289F53C8');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C622F5EE4C');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C69395C3F3');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6DF595129');
        $this->addSql('ALTER TABLE technician_on_call_extranet_user DROP FOREIGN KEY FK_2BEA67B1EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_extranet_user DROP FOREIGN KEY FK_2BEA67B1D2CDD54B');
        $this->addSql('ALTER TABLE technician_on_call_backlog_report DROP FOREIGN KEY FK_6BA995A0E8E5F9D1');
        $this->addSql('ALTER TABLE technician_on_call_backlog_association DROP FOREIGN KEY FK_7995E6AFF1F06ABE');
        $this->addSql('ALTER TABLE technician_on_call_backlog_association DROP FOREIGN KEY FK_7995E6AFEC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_files DROP FOREIGN KEY FK_9DF165E0EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_files DROP FOREIGN KEY FK_9DF165E0BF396750');
        $this->addSql('ALTER TABLE technician_on_call_main_files DROP FOREIGN KEY FK_2B41AB3BEC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_main_files DROP FOREIGN KEY FK_2B41AB3BBF396750');
        $this->addSql('ALTER TABLE technician_on_call_oldest_report DROP FOREIGN KEY FK_E5BBFCF9EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_oldest_report DROP FOREIGN KEY FK_E5BBFCF9E8E5F9D1');
        $this->addSql('ALTER TABLE technician_on_call_part DROP FOREIGN KEY FK_E9E87906B03A8386');
        $this->addSql('ALTER TABLE technician_on_call_part DROP FOREIGN KEY FK_E9E87906EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_survey DROP FOREIGN KEY FK_1F7954F1EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_survey DROP FOREIGN KEY FK_1F7954F1B03A8386');
        $this->addSql('ALTER TABLE technician_on_call_tags DROP FOREIGN KEY FK_CF5B9DE6BF396750');
        $this->addSql('ALTER TABLE technician_on_call_tags_xref DROP FOREIGN KEY FK_FDF1C1138F4AC1C');
        $this->addSql('ALTER TABLE technician_on_call_tags_xref DROP FOREIGN KEY FK_FDF1C113EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_zone_country DROP FOREIGN KEY FK_791F7C169F2C3FAB');
        $this->addSql('ALTER TABLE technician_on_call_zone_country DROP FOREIGN KEY FK_791F7C16F92F3E70');
        $this->addSql('ALTER TABLE spare_parts_requests_toc DROP FOREIGN KEY FK_5C3E7598EC02A7D0');
        $this->addSql('DROP INDEX IDX_5C3E7598EC02A7D0 ON spare_parts_requests_toc');
        $this->addSql('ALTER TABLE spare_parts_requests_toc DROP technician_on_call_id');
        $this->addSql('ALTER TABLE toc_hour_meter_transactions DROP FOREIGN KEY FK_59C11EC02A7D0');
        $this->addSql('DROP INDEX IDX_59C11EC02A7D0 ON toc_hour_meter_transactions');
        $this->addSql('ALTER TABLE toc_hour_meter_transactions DROP technician_on_call_id, CHANGE toc_legacy_id toc_legacy_id INT NOT NULL');
        $this->addSql('ALTER TABLE customer_service_record DROP FOREIGN KEY FK_3C9EAE6DEC02A7D0');
        $this->addSql('DROP INDEX IDX_3C9EAE6DEC02A7D0 ON customer_service_record');
        $this->addSql('ALTER TABLE customer_service_record DROP technician_on_call_id');
        $this->addSql('DROP TABLE technician_on_call_extranet_user');
        $this->addSql('DROP TABLE technician_on_call_backlog_report');
        $this->addSql('DROP TABLE technician_on_call_backlog_association');
        $this->addSql('DROP TABLE technician_on_call_files');
        $this->addSql('DROP TABLE technician_on_call_main_files');
        $this->addSql('DROP TABLE technician_on_call_oldest_report');
        $this->addSql('DROP TABLE technician_on_call_part');
        $this->addSql('DROP TABLE technician_on_call_survey');
        $this->addSql('DROP TABLE technician_on_call_tags');
        $this->addSql('DROP TABLE technician_on_call_tags_xref');
        $this->addSql('DROP TABLE technician_on_call_zone');
        $this->addSql('DROP TABLE technician_on_call_zone_country');
        $this->addSql('DROP TABLE technician_on_call');
        $this->addSql('DROP TABLE technician_on_call_type');
        $this->addSql('DROP TABLE service_activity');
        $this->addSql('ALTER TABLE ai_logs CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE non_conformity CHANGE location_id location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE directory_position CHANGE mentor_mandatory mentor_mandatory TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE equipment_records CHANGE state state VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE modules CHANGE disabledForTroubleTicket disabledForTroubleTicket TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE ai_responses CHANGE content content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
