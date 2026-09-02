<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180109085116 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE equipment_maintenance_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', maintenance_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_4B3D2F10F6C202BC (maintenance_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment_maintenances (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', maitenance_date DATE NOT NULL, comment TEXT NOT NULL, type INT NOT NULL COMMENT \'(DC2Type:integer)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipment_maintenance_files ADD CONSTRAINT FK_4B3D2F10F6C202BC FOREIGN KEY (maintenance_id) REFERENCES equipment_maintenances (id)');
        $this->addSql('ALTER TABLE equipment_maintenance_files ADD CONSTRAINT FK_4B3D2F10BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE files CHANGE poster_id poster_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE equipment_accidents CHANGE accident_date accident_date DATE NOT NULL');
        $this->addSql('ALTER TABLE follow_up_reports ADD equipment_maintenance_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE updated_by updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE equipment_accident_id equipment_accident_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE hourmeter_date hourmeter_date DATE NOT NULL, CHANGE odometer odometer INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE odometer_date odometer_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE follow_up_reports ADD CONSTRAINT FK_99D753AF23C256BD FOREIGN KEY (equipment_maintenance_id) REFERENCES equipment_maintenances (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_99D753AF23C256BD ON follow_up_reports (equipment_maintenance_id)');
        $this->addSql('ALTER TABLE equipment_accidents_files CHANGE accident_id accident_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE equipment_records CHANGE parent_equipment_id parent_equipment_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE airport_id airport_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE customer_serial_number customer_serial_number VARCHAR(30) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE model model VARCHAR(30) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE type type VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE location location VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE follow_up_reports DROP FOREIGN KEY FK_99D753AF23C256BD');
        $this->addSql('ALTER TABLE equipment_maintenance_files DROP FOREIGN KEY FK_4B3D2F10F6C202BC');
        $this->addSql('DROP TABLE equipment_maintenance_files');
        $this->addSql('DROP TABLE equipment_maintenances');
        $this->addSql('ALTER TABLE equipment_accidents CHANGE accident_date accident_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE equipment_accidents_files CHANGE accident_id accident_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE equipment_records CHANGE airport_id airport_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE parent_equipment_id parent_equipment_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE customer_serial_number customer_serial_number VARCHAR(30) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE model model VARCHAR(30) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE type type VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE location location VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE files CHANGE poster_id poster_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('DROP INDEX UNIQ_99D753AF23C256BD ON follow_up_reports');
        $this->addSql('ALTER TABLE follow_up_reports DROP equipment_maintenance_id, CHANGE updated_by updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE equipment_accident_id equipment_accident_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE hourmeter_date hourmeter_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE odometer odometer INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE odometer_date odometer_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }
}
