<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230903083339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crab (id INT AUTO_INCREMENT NOT NULL, department_id INT NOT NULL, equipment_record_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, fixed_by_id INT DEFAULT NULL, inspected_by_id INT DEFAULT NULL, code_id INT DEFAULT NULL, non_conformity_id INT DEFAULT NULL, derogation_id INT DEFAULT NULL, part_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, fixed_at DATETIME DEFAULT NULL, inspected_at DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, fixing_comments VARCHAR(255) DEFAULT NULL, inspecting_comments VARCHAR(255) DEFAULT NULL, category VARCHAR(50) NOT NULL, eap_id INT DEFAULT NULL, pi_question_id INT DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_80F8615DAE80F5DF (department_id), INDEX IDX_80F8615D9FC03375 (equipment_record_id), INDEX IDX_80F8615DB03A8386 (created_by_id), INDEX IDX_80F8615DC38008F2 (fixed_by_id), INDEX IDX_80F8615D475EA6BE (inspected_by_id), INDEX IDX_80F8615D27DAFE17 (code_id), INDEX IDX_80F8615D8EA30491 (non_conformity_id), INDEX IDX_80F8615DBD69C3F1 (derogation_id), UNIQUE INDEX UNIQ_80F8615D4CE34BEC (part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crab_code (id INT AUTO_INCREMENT NOT NULL, code INT NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crab_department (id INT AUTO_INCREMENT NOT NULL, derogation_position_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_5D4C0AC76E411923 (derogation_position_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crab_file (id INT NOT NULL, crab_id INT DEFAULT NULL, INDEX IDX_12F76734CDA5589B (crab_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crab_main_file (id INT NOT NULL, crab_id INT DEFAULT NULL, INDEX IDX_82DAD87DCDA5589B (crab_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crab_part (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE derogation (id INT AUTO_INCREMENT NOT NULL, assignor_id INT DEFAULT NULL, assignee_id INT DEFAULT NULL, short_description LONGTEXT NOT NULL, description LONGTEXT NOT NULL, due_date DATE NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_E46E3F3ADE920AE7 (assignor_id), INDEX IDX_E46E3F3A59EC7D60 (assignee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615DAE80F5DF FOREIGN KEY (department_id) REFERENCES crab_department (id)');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615D9FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615DC38008F2 FOREIGN KEY (fixed_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615D475EA6BE FOREIGN KEY (inspected_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615D27DAFE17 FOREIGN KEY (code_id) REFERENCES crab_code (id)');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615D8EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615DBD69C3F1 FOREIGN KEY (derogation_id) REFERENCES derogation (id)');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615D4CE34BEC FOREIGN KEY (part_id) REFERENCES crab_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crab_department ADD CONSTRAINT FK_5D4C0AC76E411923 FOREIGN KEY (derogation_position_id) REFERENCES directory_position (id)');
        $this->addSql('ALTER TABLE crab_file ADD CONSTRAINT FK_12F76734CDA5589B FOREIGN KEY (crab_id) REFERENCES crab (id)');
        $this->addSql('ALTER TABLE crab_file ADD CONSTRAINT FK_12F76734BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crab_main_file ADD CONSTRAINT FK_82DAD87DCDA5589B FOREIGN KEY (crab_id) REFERENCES crab (id)');
        $this->addSql('ALTER TABLE crab_main_file ADD CONSTRAINT FK_82DAD87DBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crab_part ADD CONSTRAINT FK_D76721E2BF396750 FOREIGN KEY (id) REFERENCES parts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE derogation ADD CONSTRAINT FK_E46E3F3ADE920AE7 FOREIGN KEY (assignor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE derogation ADD CONSTRAINT FK_E46E3F3A59EC7D60 FOREIGN KEY (assignee_id) REFERENCES user (id)');

        $this->addSql('ALTER TABLE equipment_records ADD yellow_tag_date DATETIME DEFAULT NULL, ADD first_green_tag_date DATETIME DEFAULT NULL');

        $this->addSql('ALTER TABLE non_conformity ADD crab_linked INT DEFAULT NULL');
        $this->addSql('UPDATE non_conformity SET crab_linked = crab_id');
        $this->addSql('UPDATE non_conformity SET crab_id = NULL');

        $this->addSql('ALTER TABLE non_conformity ADD CONSTRAINT FK_9726A49ACDA5589B FOREIGN KEY (crab_id) REFERENCES crab (id)');
        $this->addSql('CREATE INDEX IDX_9726A49ACDA5589B ON non_conformity (crab_id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEROGATION_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEROGATION_STATUS_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEROGATION_DUE_DATE_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CRAB_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CRAB_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CRAB_FILE_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CRAB_FILE_CHANGE_VISIBILITY")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CRAB_FIX_INSPECT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CRAB_ADMIN_PART")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_LINK_DEROGATION")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEROGATION_PARTIAL_UPDATE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_PARTIAL_UPDATE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "GG_QUALITY")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_CREATE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_PM", "ROLE_QAM", "GG_QUALITY", "ROLE_WS", "ROLE_PSM", "ROLE_EM", "ROLE_GL")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_STATUS_ADMIN"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "GG_QUALITY", "ROLE_PM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_DUE_DATE_ADMIN"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CRAB_EDIT"
                          AND user_group.name IN ("SUPERUSER", "GG_QUALITY", "ROLE_QAM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CRAB_DELETE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CRAB_FILE_DELETE"
                          AND user_group.name IN ("SUPERUSER", "GG_QUALITY", "ROLE_QAM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CRAB_FILE_CHANGE_VISIBILITY"
                          AND user_group.name IN ("SUPERUSER", "GG_QUALITY", "ROLE_QAM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CRAB_FIX_INSPECT"
                          AND user_group.name IN ("SUPERUSER", "GG_QUALITY", "ROLE_QAM", "ROLE_PM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CRAB_ADMIN_PART"
                          AND user_group.name IN ("SUPERUSER", "GG_QUALITY", "ROLE_QAM", "ROLE_PM")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_LINK_DEROGATION"
                          AND user_group.name IN ("SUPERUSER", "GG_QUALITY", "ROLE_QAM")');

        $this->addSql("INSERT IGNORE INTO crab_code (code, description) VALUES
            (010, 'Not Clean / Not removed'),
            (020, 'Not greased'),
            (030, 'Fluid leaks: hydraulic oil, fuel, coolant'),
            (035, 'Air Leak'),
            (060, 'Run tests not completed (or to redo)'),
            (080, 'Electrical function fails'),
            (070, 'Unit will not start or run'),
            (090, 'Mechanical function fails'),
            (100, 'Unit vibrates'),
            (110, 'Adjusted incorrectly \ loose parts'),
            (120, 'Defective part'),
            (125, 'Assembled incorrectly'),
            (130, 'Wrong electrical connection'),
            (170, 'Elec. insulation tear or defective'),
            (190, 'Missing part or material (Assembly)'),
            (260, 'Rust'),
            (270, 'Paint requires touch up'),
            (280, 'Document missing or incomplete'),
            (290, 'Not prepared for shipping'),
            (281, 'Serial number uncorrect or missing'),
            (075, 'Programming issue'),
            (175, 'Movement interference (elec / hyd / mec)'),
            (045, 'Wrong fluid level'),
            (085, 'Hydraulic function fails'),
            (087, 'Torquing marking missing (hyd / mec /elec)'),
            (065, 'Values out of tolerance range'),
            (302, 'Customer require additional option to SOL'),
            (304, 'Special instruction - reminder to perform something'),
            (076, 'Engine issue'),
            (013, 'FOD Foreign Object Debris'),
            (306, 'SOL incomplete'),
            (031, 'Water intrusion in sealed box'),
            (105, 'Rework  routing (elec / hyd)'),
            (299, 'FAI required'),
            (999, 'Archive (Deleted code)'),
            (301, 'Customer inspection issue'),
            (340, 'Missing option or spare parts'),
            (036, 'Rotolock Connection Leak'),
            (037, 'Rotolock Valve Leak'),
            (038, 'Steel-Copper Braze Joint Leak'),
            (039, 'Copper-Copper Braze Joint Leak'),
            (040, 'Refrigerant fluid Flare Leak'),
            (041, 'Refrigerant fluid Gasket/Flange Leak'),
            (042, 'Refrigerant fluid hose Crimp Leak'),
            (043, 'Refrigerant Fluid O-ring Leak'),
            (121, 'Defective drawing'),
            (131, 'Miswiring Issue'),
            (191, 'Parts Shortage (warehouse)'),
            (307, 'Design Error'),
            (305, 'Version Compatibility Issue'),
            (285, 'Yellow Tag Required'),
            (303, 'Service Bulletin SB');
        ");

        $this->addSql("INSERT IGNORE INTO crab_department (derogation_position_id, name) VALUES
            (19, 'Assembly'),
            (21, 'Customer'),
            (18, 'Electrical'),
            (18, 'Engineering'),
            (19, 'Paint'),
            (19, 'Prior to Shipping'),
            (21, 'Product Support'),
            (20, 'Purchasing'),
            (22, 'Quality Assurance'),
            (19, 'Refrigeration'),
            (21, 'Sales'),
            (19, 'Sub Assembly'),
            (19, 'Test'),
            (19, 'Unknown'),
            (20, 'Vendor'),
            (20, 'Warehouse');
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity DROP FOREIGN KEY FK_9726A49ACDA5589B');
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615DAE80F5DF');
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615D9FC03375');
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615DB03A8386');
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615DC38008F2');
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615D475EA6BE');
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615D27DAFE17');
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615D8EA30491');
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615DBD69C3F1');
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615D4CE34BEC');
        $this->addSql('ALTER TABLE crab_department DROP FOREIGN KEY FK_5D4C0AC76E411923');
        $this->addSql('ALTER TABLE crab_file DROP FOREIGN KEY FK_12F76734CDA5589B');
        $this->addSql('ALTER TABLE crab_file DROP FOREIGN KEY FK_12F76734BF396750');
        $this->addSql('ALTER TABLE crab_main_file DROP FOREIGN KEY FK_82DAD87DCDA5589B');
        $this->addSql('ALTER TABLE crab_main_file DROP FOREIGN KEY FK_82DAD87DBF396750');
        $this->addSql('ALTER TABLE crab_part DROP FOREIGN KEY FK_D76721E2BF396750');
        $this->addSql('ALTER TABLE derogation DROP FOREIGN KEY FK_E46E3F3ADE920AE7');
        $this->addSql('ALTER TABLE derogation DROP FOREIGN KEY FK_E46E3F3A59EC7D60');
        $this->addSql('DROP TABLE crab');
        $this->addSql('DROP TABLE crab_code');
        $this->addSql('DROP TABLE crab_department');
        $this->addSql('DROP TABLE crab_file');
        $this->addSql('DROP TABLE crab_main_file');
        $this->addSql('DROP TABLE crab_part');
        $this->addSql('DROP TABLE derogation');
        $this->addSql('ALTER TABLE equipment_records DROP yellow_tag_date, DROP first_green_tag_date');
        $this->addSql('DROP INDEX IDX_9726A49ACDA5589B ON non_conformity');
        $this->addSql('ALTER TABLE non_conformity DROP crab_linked');
    }
}
