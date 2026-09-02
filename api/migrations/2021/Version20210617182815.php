<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210617182815 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE spare_parts_requests (id INT AUTO_INCREMENT NOT NULL, poster_id INT NOT NULL, sph_id INT NOT NULL, sso_id INT NOT NULL, factory_id INT NOT NULL, shipping_origin_id INT DEFAULT NULL, erp_location_id INT DEFAULT NULL, customer_id INT NOT NULL, airport_id INT NOT NULL, delivery_address_id INT NOT NULL, created_at DATETIME NOT NULL, activity VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, shipping_date DATETIME DEFAULT NULL, estimated_shipping_date DATETIME DEFAULT NULL, notes LONGTEXT DEFAULT NULL, delivery_notes LONGTEXT DEFAULT NULL, sales_order INT DEFAULT NULL, status VARCHAR(255) NOT NULL, legacy_id INT NOT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_27DA4CF25BB66C05 (poster_id), INDEX IDX_27DA4CF2A234FDCD (sph_id), INDEX IDX_27DA4CF27843BFA4 (sso_id), INDEX IDX_27DA4CF2C7AF27D2 (factory_id), INDEX IDX_27DA4CF2105722FB (shipping_origin_id), INDEX IDX_27DA4CF234BAA6FC (erp_location_id), INDEX IDX_27DA4CF29395C3F3 (customer_id), INDEX IDX_27DA4CF2289F53C8 (airport_id), INDEX IDX_27DA4CF2EBF23851 (delivery_address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spare_parts_request_equipment_record (spare_parts_request_id INT NOT NULL, equipment_record_id INT NOT NULL, INDEX IDX_B476A024D78E718A (spare_parts_request_id), INDEX IDX_B476A0249FC03375 (equipment_record_id), PRIMARY KEY(spare_parts_request_id, equipment_record_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spare_parts_requests_delivery_addresses (id INT AUTO_INCREMENT NOT NULL, contact_id INT DEFAULT NULL, airport_id INT DEFAULT NULL, poster_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME DEFAULT NULL, address_country VARCHAR(2) DEFAULT NULL, address_street1 VARCHAR(255) DEFAULT NULL, address_street2 VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(20) DEFAULT NULL, address_city VARCHAR(50) DEFAULT NULL, address_town VARCHAR(50) DEFAULT NULL, address_state VARCHAR(50) DEFAULT NULL, INDEX IDX_93076387E7A1254A (contact_id), INDEX IDX_93076387289F53C8 (airport_id), INDEX IDX_930763875BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spare_parts_requests_parts (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, spare_parts_request_id INT NOT NULL, deleted_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL, part_number VARCHAR(25) NOT NULL, description VARCHAR(255) NOT NULL, unit_of_measure VARCHAR(3) DEFAULT NULL, quantity INT NOT NULL, comment LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_52CA4FA0B03A8386 (created_by_id), INDEX IDX_52CA4FA0D78E718A (spare_parts_request_id), INDEX IDX_52CA4FA0C76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spare_parts_requests_proof_of_delivery_files (id INT NOT NULL, spare_parts_request_id INT DEFAULT NULL, INDEX IDX_83693E04D78E718A (spare_parts_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spare_parts_requests_toc (id INT NOT NULL, toc_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spare_parts_requests_sb (id INT NOT NULL, sb_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE spare_parts_requests_sb ADD CONSTRAINT FK_B3850684BF396750 FOREIGN KEY (id) REFERENCES spare_parts_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE spare_parts_requests ADD CONSTRAINT FK_27DA4CF25BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE spare_parts_requests ADD CONSTRAINT FK_27DA4CF2A234FDCD FOREIGN KEY (sph_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE spare_parts_requests ADD CONSTRAINT FK_27DA4CF27843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE spare_parts_requests ADD CONSTRAINT FK_27DA4CF2C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE spare_parts_requests ADD CONSTRAINT FK_27DA4CF2105722FB FOREIGN KEY (shipping_origin_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE spare_parts_requests ADD CONSTRAINT FK_27DA4CF234BAA6FC FOREIGN KEY (erp_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE spare_parts_requests ADD CONSTRAINT FK_27DA4CF29395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE spare_parts_requests ADD CONSTRAINT FK_27DA4CF2289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('ALTER TABLE spare_parts_requests ADD CONSTRAINT FK_27DA4CF2EBF23851 FOREIGN KEY (delivery_address_id) REFERENCES spare_parts_requests_delivery_addresses (id)');
        $this->addSql('ALTER TABLE spare_parts_request_equipment_record ADD CONSTRAINT FK_B476A024D78E718A FOREIGN KEY (spare_parts_request_id) REFERENCES spare_parts_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE spare_parts_request_equipment_record ADD CONSTRAINT FK_B476A0249FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE spare_parts_requests_delivery_addresses ADD CONSTRAINT FK_93076387E7A1254A FOREIGN KEY (contact_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE spare_parts_requests_delivery_addresses ADD CONSTRAINT FK_93076387289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('ALTER TABLE spare_parts_requests_delivery_addresses ADD CONSTRAINT FK_930763875BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE spare_parts_requests_parts ADD CONSTRAINT FK_52CA4FA0B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE spare_parts_requests_parts ADD CONSTRAINT FK_52CA4FA0D78E718A FOREIGN KEY (spare_parts_request_id) REFERENCES spare_parts_requests (id)');
        $this->addSql('ALTER TABLE spare_parts_requests_parts ADD CONSTRAINT FK_52CA4FA0C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE spare_parts_requests_proof_of_delivery_files ADD CONSTRAINT FK_83693E04D78E718A FOREIGN KEY (spare_parts_request_id) REFERENCES spare_parts_requests (id)');
        $this->addSql('ALTER TABLE spare_parts_requests_proof_of_delivery_files ADD CONSTRAINT FK_83693E04BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE spare_parts_requests_toc ADD CONSTRAINT FK_5C3E7598BF396750 FOREIGN KEY (id) REFERENCES spare_parts_requests (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE spare_parts_requests AUTO_INCREMENT=16689');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SPARE_PARTS_REQUESTS_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SPARE_PARTS_REQUESTS_EDIT_PARTS")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SPARE_PARTS_REQUESTS_CREATE"
                          AND user_group.name in ("SUPERUSER", "GG_PARTS", "GG_SERVICE")'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SPARE_PARTS_REQUESTS_EDIT_PARTS"
                          AND user_group.name in ("SUPERUSER", "GG_PARTS", "GG_SERVICE")'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL"
                          AND user_group.name in ("SUPERUSER", "GG_PARTS")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spare_parts_request_equipment_record DROP FOREIGN KEY FK_B476A024D78E718A');
        $this->addSql('ALTER TABLE spare_parts_requests_parts DROP FOREIGN KEY FK_52CA4FA0D78E718A');
        $this->addSql('ALTER TABLE spare_parts_requests_proof_of_delivery_files DROP FOREIGN KEY FK_83693E04D78E718A');
        $this->addSql('ALTER TABLE spare_parts_requests_toc DROP FOREIGN KEY FK_5C3E7598BF396750');
        $this->addSql('ALTER TABLE spare_parts_requests DROP FOREIGN KEY FK_27DA4CF2EBF23851');
        $this->addSql('DROP TABLE spare_parts_requests');
        $this->addSql('DROP TABLE spare_parts_request_equipment_record');
        $this->addSql('DROP TABLE spare_parts_requests_delivery_addresses');
        $this->addSql('DROP TABLE spare_parts_requests_parts');
        $this->addSql('DROP TABLE spare_parts_requests_proof_of_delivery_files');
        $this->addSql('DROP TABLE spare_parts_requests_toc');
    }
}
