<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210218165328 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contract_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, UNIQUE INDEX unique_contract_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE directory_position_category (id INT AUTO_INCREMENT NOT NULL, position_category_type_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(500) NOT NULL, direct_headcount TINYINT(1) NOT NULL, INDEX IDX_AC34330244055787 (position_category_type_id), UNIQUE INDEX unique_position_category_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE position_category_division (position_category_id INT NOT NULL, division_id INT NOT NULL, INDEX IDX_A4CDAB2FFD0FFEE (position_category_id), INDEX IDX_A4CDAB241859289 (division_id), PRIMARY KEY(position_category_id, division_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE directory_position_category_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX unique_position_category_type_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE directory_position_classification (id INT AUTO_INCREMENT NOT NULL, business_unit_id INT DEFAULT NULL, position_category_id INT DEFAULT NULL, INDEX IDX_2302D94BA58ECB40 (business_unit_id), INDEX IDX_2302D94BFFD0FFEE (position_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE position_classification_position (position_classification_id INT NOT NULL, position_id INT NOT NULL, INDEX IDX_C9C47BCF87A74C42 (position_classification_id), INDEX IDX_C9C47BCFDD842E46 (position_id), PRIMARY KEY(position_classification_id, position_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE directory_position_category ADD CONSTRAINT FK_AC34330244055787 FOREIGN KEY (position_category_type_id) REFERENCES directory_position_category_type (id)');
        $this->addSql('ALTER TABLE position_category_division ADD CONSTRAINT FK_A4CDAB2FFD0FFEE FOREIGN KEY (position_category_id) REFERENCES directory_position_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE position_category_division ADD CONSTRAINT FK_A4CDAB241859289 FOREIGN KEY (division_id) REFERENCES directory_division (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE directory_position_classification ADD CONSTRAINT FK_2302D94BA58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('ALTER TABLE directory_position_classification ADD CONSTRAINT FK_2302D94BFFD0FFEE FOREIGN KEY (position_category_id) REFERENCES directory_position_category (id)');
        $this->addSql('ALTER TABLE position_classification_position ADD CONSTRAINT FK_C9C47BCF87A74C42 FOREIGN KEY (position_classification_id) REFERENCES directory_position_classification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE position_classification_position ADD CONSTRAINT FK_C9C47BCFDD842E46 FOREIGN KEY (position_id) REFERENCES directory_position (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD contract_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649CD1DF15B FOREIGN KEY (contract_type_id) REFERENCES contract_type (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649CD1DF15B ON user (contract_type_id)');

        // INSERT FEATURES
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CONTRACT_TYPE_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CONTRACT_TYPE_ADMIN"
                          AND user_group.name in ("SUPERUSER", "ROLE_GTCD")'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POSITION_CATEGORY_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_POSITION_CATEGORY_WRITE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_GTCD")'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POSITION_CLASSIFICATION_WRITE_FULL")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_POSITION_CLASSIFICATION_WRITE_FULL"
                          AND user_group.name IN ("SUPERUSER", "ROLE_GTCD")'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POSITION_CLASSIFICATION_WRITE_BU")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_POSITION_CLASSIFICATION_WRITE_BU"
                          AND user_group.name IN ("SUPERUSER", "GG_HR")'
        );

        // INSERT CONTRACT TYPES
        $this->addSql('INSERT IGNORE INTO contract_type (name, description) VALUES("Regular Employee", "Indefinite time employment or multi year Work Contract")');
        $this->addSql('INSERT IGNORE INTO contract_type (name, description) VALUES("Short term contract  employee", "Medium or Short Term Work Contract")');
        $this->addSql('INSERT IGNORE INTO contract_type (name, description) VALUES("Temporary employees Interim & consultants", "Temps & long term Consulting")');
        $this->addSql('INSERT IGNORE INTO contract_type (name, description) VALUES("Apprentices", "Apprentices")');
        $this->addSql('INSERT IGNORE INTO contract_type (name, description) VALUES("Trainees", "Trainees, Interns")');

        // INSERT POSITION CATEGORY TYPES
        $this->addSql('INSERT IGNORE INTO directory_position_category_type (id, name) VALUES(1, "Manufacturing Entities")');
        $this->addSql('INSERT IGNORE INTO directory_position_category_type (id, name) VALUES(2, "Contracted Single Source Location or Storefront Warehouse")');
        $this->addSql('INSERT IGNORE INTO directory_position_category_type (id, name) VALUES(3, "Project Design")');
        $this->addSql('INSERT IGNORE INTO directory_position_category_type (id, name) VALUES(4, "Sales, Service & Parts")');
        $this->addSql('INSERT IGNORE INTO directory_position_category_type (id, name) VALUES(5, "G&A")');

        // INSERT POSITION CATEGORIES
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(1, "Engineering", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(5, "Finance & Accounting", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(5, "General Management", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(5, "Human Ressources", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(5, "Management of Information System", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(2, "Material Control", "", 1)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(1, "Material control & Purchasing", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(1, "Product Support & Marketing", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(1, "Production Management", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(3, "Publication designer", "", 1)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(1, "Quality Assurance", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(4, "Sales", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(4, "Sales Administration", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(4, "Service Management", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(4, "Service Technician & Deployment & Site Engineers", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(1, "Shipping & Expediting", "", 1)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(2, "Single source warehouse keepers", "", 1)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(4, "Spare Parts Management", "", 0)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(2, "Warehouse keepers", "", 1)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(1, "Workers (Manufacturing or Assembly)", "", 1)');
        $this->addSql('INSERT IGNORE INTO directory_position_category (position_category_type_id, name, description, direct_headcount) VALUES(1, "Workers (Test & Control)", "", 1)');

        // ENABLE POSITION CATEGORIES ON ALL DIVISIONS
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Engineering"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Finance & Accounting"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="General Management"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Human Ressources"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Management of Information System"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Material Control"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Material control & Purchasing"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Product Support & Marketing"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Production Management"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Publication designer"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Quality Assurance"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Sales"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Sales Administration"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Service Management"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Service Technician & Deployment & Site Engineers"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Shipping & Expediting"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Single source warehouse keepers"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Spare Parts Management"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Warehouse keepers"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Workers (Manufacturing or Assembly)"');
        $this->addSql('INSERT IGNORE INTO position_category_division (position_category_id, division_id) SELECT dpc.id, dd.id FROM directory_division dd, directory_position_category dpc WHERE dpc.name="Workers (Test & Control)"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649CD1DF15B');
        $this->addSql('ALTER TABLE position_category_division DROP FOREIGN KEY FK_A4CDAB2FFD0FFEE');
        $this->addSql('ALTER TABLE directory_position_classification DROP FOREIGN KEY FK_2302D94BFFD0FFEE');
        $this->addSql('ALTER TABLE directory_position_category DROP FOREIGN KEY FK_AC34330244055787');
        $this->addSql('ALTER TABLE position_classification_position DROP FOREIGN KEY FK_C9C47BCF87A74C42');
        $this->addSql('DROP TABLE contract_type');
        $this->addSql('DROP TABLE directory_position_category');
        $this->addSql('DROP TABLE position_category_division');
        $this->addSql('DROP TABLE directory_position_category_type');
        $this->addSql('DROP TABLE directory_position_classification');
        $this->addSql('DROP TABLE position_classification_position');
        $this->addSql('DROP INDEX IDX_8D93D649CD1DF15B ON user');
        $this->addSql('ALTER TABLE user DROP contract_type_id');
    }
}
