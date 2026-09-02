<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116125929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service_activity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_550252675E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technician_on_call (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, equipment_record_id INT DEFAULT NULL, assignee_id INT DEFAULT NULL, unit_operational_status_id INT DEFAULT NULL, technician_on_call_type_id INT DEFAULT NULL, service_activity_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, deletedAt DATETIME DEFAULT NULL, error_codes LONGTEXT DEFAULT NULL, indice_factor VARCHAR(255) NOT NULL, estimated_hours INT NOT NULL, symptoms LONGTEXT DEFAULT NULL, root_cause LONGTEXT DEFAULT NULL, solution LONGTEXT DEFAULT NULL, INDEX IDX_3BD0B5C6B03A8386 (created_by_id), INDEX IDX_3BD0B5C69FC03375 (equipment_record_id), INDEX IDX_3BD0B5C659EC7D60 (assignee_id), INDEX IDX_3BD0B5C64D735310 (unit_operational_status_id), INDEX IDX_3BD0B5C6BB4FD6E1 (technician_on_call_type_id), INDEX IDX_3BD0B5C64D5A463A (service_activity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technician_on_call_tags (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technician_on_call_tags_xref (technician_on_call_tag_id INT NOT NULL, technician_on_call_id INT NOT NULL, INDEX IDX_FDF1C1138F4AC1C (technician_on_call_tag_id), INDEX IDX_FDF1C113EC02A7D0 (technician_on_call_id), PRIMARY KEY(technician_on_call_tag_id, technician_on_call_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technician_on_call_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_2C395EE95E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C69FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C659EC7D60 FOREIGN KEY (assignee_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C64D735310 FOREIGN KEY (unit_operational_status_id) REFERENCES unit_operational_statuses (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6BB4FD6E1 FOREIGN KEY (technician_on_call_type_id) REFERENCES technician_on_call_type (id)');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C64D5A463A FOREIGN KEY (service_activity_id) REFERENCES service_activity (id)');
        $this->addSql('ALTER TABLE technician_on_call_tags ADD CONSTRAINT FK_CF5B9DE6BF396750 FOREIGN KEY (id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_tags_xref ADD CONSTRAINT FK_FDF1C1138F4AC1C FOREIGN KEY (technician_on_call_tag_id) REFERENCES technician_on_call_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_tags_xref ADD CONSTRAINT FK_FDF1C113EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 23385,"toc.tags.ibs",NOW(),"toc")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 23385,"toc.tags.ihs",NOW(),"toc")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 23385,"toc.tags.link",NOW(),"toc")');
        $this->addSql('INSERT IGNORE INTO tags (created_by,name,created_at, discr) VALUES ( 23385,"toc.tags.apu_off",NOW(),"toc")');
        $this->addSql('INSERT IGNORE INTO technician_on_call_tags (id) SELECT id FROM tags WHERE discr="toc"');

        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Troubleshooting", "Troubleshooting")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Commissioning", "Commissioning")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Service Bulletin", "Service Bulletin")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Training", "Training")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Maintenance", "Maintenance")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Unit Upgrade", "Unit Upgrade")');
        $this->addSql('INSERT IGNORE INTO service_activity (name, description) VALUES ( "Info request", "Info request")');

        $this->addSql('INSERT IGNORE INTO technician_on_call_type (name, description) VALUES ( "toc.type.not_define_yet", "Not Defined Yet")');
        $this->addSql('INSERT IGNORE INTO technician_on_call_type (name, description) VALUES ( "toc.type.customer", "Customer (Payable Service)")');
        $this->addSql('INSERT IGNORE INTO technician_on_call_type (name, description) VALUES ( "toc.type.sso", "SSO (Sales Concession)")');
        $this->addSql('INSERT IGNORE INTO technician_on_call_type (name, description) VALUES ( "toc.type.factory", "Factory (Warranty, Compulsory SB…)")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6B03A8386');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C69FC03375');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C659EC7D60');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C64D735310');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6BB4FD6E1');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C64D5A463A');
        $this->addSql('ALTER TABLE technician_on_call_tags DROP FOREIGN KEY FK_CF5B9DE6BF396750');
        $this->addSql('ALTER TABLE technician_on_call_tags_xref DROP FOREIGN KEY FK_FDF1C1138F4AC1C');
        $this->addSql('ALTER TABLE technician_on_call_tags_xref DROP FOREIGN KEY FK_FDF1C113EC02A7D0');
        $this->addSql('DROP TABLE service_activity');
        $this->addSql('DROP TABLE technician_on_call');
        $this->addSql('DROP TABLE technician_on_call_tags');
        $this->addSql('DROP TABLE technician_on_call_tags_xref');
        $this->addSql('DROP TABLE technician_on_call_type');

        $this->addSql('DELETE FROM tags WHERE discr="toc"');
    }
}
