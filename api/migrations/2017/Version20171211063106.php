<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171211063106 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE countries CHANGE fips_code fips_code VARCHAR(10) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE fips_name fips_name VARCHAR(100) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE latitude latitude VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE longitude longitude VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE iata_codes CHANGE country_id country_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE city_code_3 city_code_3 VARCHAR(3) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE state state VARCHAR(60) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE name name VARCHAR(22) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE equipment_records ADD airport_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE parent_equipment_id parent_equipment_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE customer_serial_number customer_serial_number VARCHAR(30) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE model model VARCHAR(30) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE type type VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE location location VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A9289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('CREATE INDEX IDX_EAE697A9289F53C8 ON equipment_records (airport_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE countries CHANGE fips_code fips_code VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE fips_name fips_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE latitude latitude VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE longitude longitude VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE equipment_records DROP FOREIGN KEY FK_EAE697A9289F53C8');
        $this->addSql('DROP INDEX IDX_EAE697A9289F53C8 ON equipment_records');
        $this->addSql('ALTER TABLE equipment_records DROP airport_id, CHANGE parent_equipment_id parent_equipment_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE customer_serial_number customer_serial_number VARCHAR(30) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE model model VARCHAR(30) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE type type VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE location location VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE iata_codes CHANGE country_id country_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE city_code_3 city_code_3 VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE state state VARCHAR(60) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE name name VARCHAR(22) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
    }
}
