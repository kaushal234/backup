<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171218153711 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE unit_operational_statuses (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(3) NOT NULL COMMENT \'(DC2Type:string)\', description VARCHAR(50) NOT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX UNIQ_16931F215E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('INSERT IGNORE INTO unit_operational_statuses (name, description)
            VALUES
            ("MCF", "Mission Capable Fully"),
            ("MCP", "Mission Capable Partially"),
            ("NMC", "Non Mission Capable")
        ');

        $this->addSql('CREATE TABLE follow_up_reports (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', created_by INT NOT NULL COMMENT \'(DC2Type:integer)\', updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', equipment_record_id INT NOT NULL COMMENT \'(DC2Type:integer)\', operational_status_id INT NOT NULL COMMENT \'(DC2Type:integer)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', comment TEXT NOT NULL, hourmeter INT NOT NULL COMMENT \'(DC2Type:integer)\', hourmeter_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', odometer INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', odometer_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_99D753AFDE12AB56 (created_by), INDEX IDX_99D753AF16FE72E1 (updated_by), INDEX IDX_99D753AF9FC03375 (equipment_record_id), INDEX IDX_99D753AFF19CA0D9 (operational_status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE follow_up_reports ADD CONSTRAINT FK_99D753AFDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE follow_up_reports ADD CONSTRAINT FK_99D753AF16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE follow_up_reports ADD CONSTRAINT FK_99D753AF9FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE follow_up_reports ADD CONSTRAINT FK_99D753AFF19CA0D9 FOREIGN KEY (operational_status_id) REFERENCES unit_operational_statuses (id)');

        $this->addSql('CREATE TABLE equipment_accidents (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', accident_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', comment TEXT NOT NULL, human_injuries TINYINT(1) NOT NULL, plane_damages TINYINT(1) NOT NULL, environment_damages TINYINT(1) NOT NULL, equipment_damages TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment_accidents_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', accident_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_268DCFD716D8554C (accident_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipment_accidents_files ADD CONSTRAINT FK_268DCFD716D8554C FOREIGN KEY (accident_id) REFERENCES equipment_accidents (id)');
        $this->addSql('ALTER TABLE equipment_accidents_files ADD CONSTRAINT FK_268DCFD7BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE files CHANGE poster_id poster_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE follow_up_reports ADD equipment_accident_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE updated_by updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE odometer odometer INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE odometer_date odometer_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE follow_up_reports ADD CONSTRAINT FK_99D753AF779AA16B FOREIGN KEY (equipment_accident_id) REFERENCES equipment_accidents (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_99D753AF779AA16B ON follow_up_reports (equipment_accident_id)');
        $this->addSql('ALTER TABLE equipment_records CHANGE parent_equipment_id parent_equipment_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE airport_id airport_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE customer_serial_number customer_serial_number VARCHAR(30) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE model model VARCHAR(30) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE type type VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE location location VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE follow_up_reports DROP FOREIGN KEY FK_99D753AF779AA16B');
        $this->addSql('ALTER TABLE equipment_accidents_files DROP FOREIGN KEY FK_268DCFD716D8554C');
        $this->addSql('DROP TABLE IF EXISTS equipment_accidents');
        $this->addSql('DROP TABLE IF EXISTS equipment_accidents_files');
        $this->addSql('ALTER TABLE files CHANGE poster_id poster_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('DROP INDEX UNIQ_99D753AF779AA16B ON follow_up_reports');
        $this->addSql('ALTER TABLE follow_up_reports DROP equipment_accident_id, CHANGE updated_by updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE odometer odometer INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE odometer_date odometer_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE follow_up_reports DROP FOREIGN KEY FK_99D753AFF19CA0D9');
        $this->addSql('DROP TABLE unit_operational_statuses');
        $this->addSql('DROP TABLE follow_up_reports');
    }
}
