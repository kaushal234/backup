<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190208202017 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sales_areas (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', country_id INT NOT NULL COMMENT \'(DC2Type:integer)\', asm_id INT NOT NULL COMMENT \'(DC2Type:integer)\', sso_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_AEF403EAF92F3E70 (country_id), INDEX IDX_AEF403EA9C54D4BF (asm_id), INDEX IDX_AEF403EA7843BFA4 (sso_id), UNIQUE INDEX unique_asm_per_country_and_network (country_id, asm_id, sso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE sales_areas ADD CONSTRAINT FK_AEF403EAF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE sales_areas ADD CONSTRAINT FK_AEF403EA9C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sales_areas ADD CONSTRAINT FK_AEF403EA7843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');

        $this->addSql('CREATE TABLE directory_networks (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX UNIQ_DD0181C65E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('INSERT INTO directory_networks VALUES (NULL, \'TLD\')');
        $this->addSql('INSERT INTO directory_networks VALUES (NULL, \'SAS\')');
        $this->addSql('INSERT INTO directory_networks VALUES (NULL, \'AERO\')');

        $this->addSql('ALTER TABLE directory_location ADD network_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE juridical_location_id juridical_location_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE representative_id representative_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE region_id region_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE domain domain VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE internal_network_address internal_network_address VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE erp erp SMALLINT DEFAULT NULL, CHANGE currency currency VARCHAR(3) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE contact_telephone contact_telephone VARCHAR(60) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE contact_fax contact_fax VARCHAR(60) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE contact_spare_parts_email contact_spare_parts_email VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE contact_spare_parts_telephone contact_spare_parts_telephone VARCHAR(60) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE contact_spare_parts_fax contact_spare_parts_fax VARCHAR(60) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE contact_service_hub_email contact_service_hub_email VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE contact_service_hub_telephone contact_service_hub_telephone VARCHAR(60) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_street1 address_street1 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_street2 address_street2 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_postal_code address_postal_code VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_city address_city VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_town address_town VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_state address_state VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE time_zone time_zone VARCHAR(40) DEFAULT NULL COMMENT \'(DC2Type:string)\'');
        $this->addSql('ALTER TABLE directory_location ADD CONSTRAINT FK_5A26BC2634128B91 FOREIGN KEY (network_id) REFERENCES directory_networks (id)');
        $this->addSql('CREATE INDEX IDX_5A26BC2634128B91 ON directory_location (network_id)');

        $this->addSql('UPDATE directory_location SET network_id=1 WHERE name LIKE "TLD%"');
        $this->addSql('UPDATE directory_location SET network_id=2 WHERE name="SAS"');
        $this->addSql('UPDATE directory_location SET network_id=3 WHERE name="AERO Specialties"');

        $this->addSql('INSERT INTO sales_areas SELECT NULL, country_id, people_id, dl.id FROM countries_asms LEFT JOIN user u on countries_asms.people_id = u.id LEFT JOIN directory_businessunit db on u.business_unit_id = db.id LEFT JOIN directory_location dl on db.location_id = dl.id');

        $this->addSql('DROP TABLE countries_asms');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_location DROP FOREIGN KEY FK_5A26BC2634128B91');
        $this->addSql('CREATE TABLE countries_asms (country_id INT NOT NULL COMMENT \'(DC2Type:integer)\', people_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_7A95D591F92F3E70 (country_id), INDEX IDX_7A95D5913147C936 (people_id), PRIMARY KEY(country_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE countries_asms ADD CONSTRAINT FK_7A95D5913147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE countries_asms ADD CONSTRAINT FK_7A95D591F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE sales_areas');
        $this->addSql('DROP TABLE directory_networks');
        $this->addSql('DROP INDEX IDX_5A26BC2634128B91 ON directory_location');
        $this->addSql('ALTER TABLE directory_location DROP network_id, CHANGE juridical_location_id juridical_location_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE representative_id representative_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE region_id region_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE domain domain VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE internal_network_address internal_network_address VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE erp erp SMALLINT DEFAULT NULL, CHANGE currency currency VARCHAR(3) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE time_zone time_zone VARCHAR(40) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE contact_telephone contact_telephone VARCHAR(60) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE contact_fax contact_fax VARCHAR(60) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE contact_spare_parts_email contact_spare_parts_email VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE contact_spare_parts_telephone contact_spare_parts_telephone VARCHAR(60) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE contact_spare_parts_fax contact_spare_parts_fax VARCHAR(60) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE contact_service_hub_email contact_service_hub_email VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE contact_service_hub_telephone contact_service_hub_telephone VARCHAR(60) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_street1 address_street1 VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_street2 address_street2 VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_postal_code address_postal_code VARCHAR(20) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_city address_city VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_town address_town VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_state address_state VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
    }
}
