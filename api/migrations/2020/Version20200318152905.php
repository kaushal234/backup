<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200318152905 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE transport_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, discr VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C43F2EC85E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shipping_quotation_requests (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, sso_id INT DEFAULT NULL, incoterms VARCHAR(255) NOT NULL, inco_location VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, estimated_pick_up_deadline VARCHAR(255) NOT NULL, note VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_B701C753B03A8386 (created_by_id), INDEX IDX_B701C7537843BFA4 (sso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shipping_quotation_request_lines (id INT AUTO_INCREMENT NOT NULL, factory_id INT DEFAULT NULL, equipment_record_id INT DEFAULT NULL, product_id INT NOT NULL, shipping_quotation_request_id INT NOT NULL, other_location TEXT DEFAULT NULL, incoterms VARCHAR(255) DEFAULT NULL, inco_location VARCHAR(255) DEFAULT NULL, transshipment_authorized TINYINT(1) NOT NULL, type VARCHAR(255) NOT NULL, estimated_pick_up_deadline VARCHAR(255) DEFAULT NULL, quantity INT NOT NULL, harmonized_system_code VARCHAR(255) NOT NULL, rolling_equipment TINYINT(1) NOT NULL, length INT NOT NULL, width INT NOT NULL, height INT NOT NULL, weight INT NOT NULL, ground_clearance INT NOT NULL, unloading_planned TINYINT(1) NOT NULL, note VARCHAR(255) DEFAULT NULL, INDEX IDX_1007837C7AF27D2 (factory_id), INDEX IDX_10078379FC03375 (equipment_record_id), INDEX IDX_10078374584665A (product_id), INDEX IDX_10078371AF83651 (shipping_quotation_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trucks (id INT AUTO_INCREMENT NOT NULL, truck_type_id INT NOT NULL, shipping_quotation_request_line_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_FB16EAE62FDA3C7 (truck_type_id), INDEX IDX_FB16EAE640B780EE (shipping_quotation_request_line_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE containers (id INT AUTO_INCREMENT NOT NULL, container_type_id INT NOT NULL, shipping_quotation_request_line_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_91ACA40B5B3408DE (container_type_id), INDEX IDX_91ACA40B40B780EE (shipping_quotation_request_line_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE freight_forwarders (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, emails TEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', supplier_number VARCHAR(255) DEFAULT NULL, language VARCHAR(2) DEFAULT NULL, INDEX IDX_CEE427B564D218E (location_id), UNIQUE INDEX unique_suno_per_location (supplier_number, location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipment_records ADD length INT DEFAULT NULL, ADD width INT DEFAULT NULL, ADD height INT DEFAULT NULL, ADD weight INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shipping_quotation_requests ADD CONSTRAINT FK_B701C753B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE shipping_quotation_requests ADD CONSTRAINT FK_B701C7537843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE shipping_quotation_request_lines ADD CONSTRAINT FK_1007837C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE shipping_quotation_request_lines ADD CONSTRAINT FK_10078379FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE shipping_quotation_request_lines ADD CONSTRAINT FK_10078374584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE shipping_quotation_request_lines ADD CONSTRAINT FK_10078371AF83651 FOREIGN KEY (shipping_quotation_request_id) REFERENCES shipping_quotation_requests (id)');
        $this->addSql('ALTER TABLE trucks ADD CONSTRAINT FK_FB16EAE62FDA3C7 FOREIGN KEY (truck_type_id) REFERENCES transport_types (id)');
        $this->addSql('ALTER TABLE trucks ADD CONSTRAINT FK_FB16EAE640B780EE FOREIGN KEY (shipping_quotation_request_line_id) REFERENCES shipping_quotation_request_lines (id)');
        $this->addSql('ALTER TABLE containers ADD CONSTRAINT FK_91ACA40B5B3408DE FOREIGN KEY (container_type_id) REFERENCES transport_types (id)');
        $this->addSql('ALTER TABLE containers ADD CONSTRAINT FK_91ACA40B40B780EE FOREIGN KEY (shipping_quotation_request_line_id) REFERENCES shipping_quotation_request_lines (id)');
        $this->addSql('ALTER TABLE freight_forwarders ADD CONSTRAINT FK_CEE427B564D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trucks DROP FOREIGN KEY FK_FB16EAE62FDA3C7');
        $this->addSql('ALTER TABLE containers DROP FOREIGN KEY FK_91ACA40B5B3408DE');
        $this->addSql('ALTER TABLE shipping_quotation_request_lines DROP FOREIGN KEY FK_10078371AF83651');
        $this->addSql('ALTER TABLE trucks DROP FOREIGN KEY FK_FB16EAE640B780EE');
        $this->addSql('ALTER TABLE containers DROP FOREIGN KEY FK_91ACA40B40B780EE');
        $this->addSql('ALTER TABLE equipment_records DROP length, DROP width, DROP height, DROP weight');
        $this->addSql('DROP TABLE transport_types');
        $this->addSql('DROP TABLE shipping_quotation_requests');
        $this->addSql('DROP TABLE shipping_quotation_request_lines');
        $this->addSql('DROP TABLE trucks');
        $this->addSql('DROP TABLE containers');
        $this->addSql('DROP TABLE freight_forwarders');
    }
}
