<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151123093304 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE directory_location (id INT AUTO_INCREMENT NOT NULL, business_unit_id INT DEFAULT NULL, juridical_location_id INT DEFAULT NULL, representative_id INT DEFAULT NULL, region_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, company VARCHAR(100) NOT NULL, domain VARCHAR(255) DEFAULT NULL, firewall VARCHAR(18) DEFAULT NULL, erp SMALLINT DEFAULT NULL, currency VARCHAR(3) DEFAULT NULL, legacy_id INT NOT NULL, capability_sso TINYINT(1) NOT NULL, capability_factory TINYINT(1) NOT NULL, capability_warehouse TINYINT(1) NOT NULL, capability_spare_parts_hub TINYINT(1) NOT NULL, capability_service_hub TINYINT(1) NOT NULL, capability_head_quarter TINYINT(1) NOT NULL, contact_telephone VARCHAR(60) DEFAULT NULL, contact_fax VARCHAR(60) DEFAULT NULL, contact_spare_parts_email VARCHAR(255) DEFAULT NULL, contact_spare_parts_telephone VARCHAR(60) DEFAULT NULL, contact_spare_parts_fax VARCHAR(60) DEFAULT NULL, contact_service_hub_email VARCHAR(255) DEFAULT NULL, contact_service_hub_telephone VARCHAR(60) DEFAULT NULL, state_public TINYINT(1) NOT NULL, state_hidden TINYINT(1) NOT NULL, state_disabled TINYINT(1) NOT NULL, address_street1 VARCHAR(255) DEFAULT NULL, address_street2 VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(20) DEFAULT NULL, address_city VARCHAR(50) DEFAULT NULL, address_town VARCHAR(50) DEFAULT NULL, address_state VARCHAR(50) DEFAULT NULL, address_country VARCHAR(2) DEFAULT NULL, UNIQUE INDEX UNIQ_5A26BC265E237E06 (name), INDEX IDX_5A26BC26A58ECB40 (business_unit_id), INDEX IDX_5A26BC265CBDD13 (juridical_location_id), INDEX IDX_5A26BC26FC3FF006 (representative_id), INDEX IDX_5A26BC2698260155 (region_id), INDEX IDX_5A26BC26FC51BA91 (erp), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE directory_region (id INT AUTO_INCREMENT NOT NULL, `label` VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_1AF6AE6BEA750E8 (`label`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE directory_location ADD CONSTRAINT FK_5A26BC26A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES business_unit (id)');
        $this->addSql('ALTER TABLE directory_location ADD CONSTRAINT FK_5A26BC265CBDD13 FOREIGN KEY (juridical_location_id) REFERENCES directory_juridical_location (id)');
        $this->addSql('ALTER TABLE directory_location ADD CONSTRAINT FK_5A26BC26FC3FF006 FOREIGN KEY (representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE directory_location ADD CONSTRAINT FK_5A26BC2698260155 FOREIGN KEY (region_id) REFERENCES directory_region (id)');
        $this->addSql('ALTER TABLE acronym CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE business_unit DROP location, DROP erp, CHANGE legacy_id legacy_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C200E5E5E237E06 ON business_unit (name)');
        $this->addSql('ALTER TABLE directory_juridical_location ADD address_street1 VARCHAR(255) DEFAULT NULL, ADD address_street2 VARCHAR(255) DEFAULT NULL, ADD address_town VARCHAR(50) DEFAULT NULL, DROP address_street, CHANGE address_postal_code address_postal_code VARCHAR(20) DEFAULT NULL, CHANGE address_city address_city VARCHAR(50) DEFAULT NULL, CHANGE address_state address_state VARCHAR(50) DEFAULT NULL, CHANGE address_country address_country VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE lastname lastname VARCHAR(50) DEFAULT NULL, CHANGE direct_phone direct_phone VARCHAR(25) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_location DROP FOREIGN KEY FK_5A26BC2698260155');
        $this->addSql('DROP TABLE directory_location');
        $this->addSql('DROP TABLE directory_region');
        $this->addSql('ALTER TABLE acronym CHANGE description description LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('DROP INDEX UNIQ_8C200E5E5E237E06 ON business_unit');
        $this->addSql('ALTER TABLE business_unit ADD location VARCHAR(60) NOT NULL COLLATE utf8_unicode_ci, ADD erp VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE legacy_id legacy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE directory_juridical_location ADD address_street VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP address_street1, DROP address_street2, DROP address_town, CHANGE address_postal_code address_postal_code VARCHAR(7) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE address_city address_city VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE address_state address_state VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE user CHANGE lastname lastname VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE direct_phone direct_phone VARCHAR(25) NOT NULL COLLATE utf8_unicode_ci');
    }
}
