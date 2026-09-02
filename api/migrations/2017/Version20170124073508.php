<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170124073508 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE customer_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, legacy_id INT NOT NULL, UNIQUE INDEX UNIQ_9705FC0B5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customers (id INT AUTO_INCREMENT NOT NULL, parent_customer_id INT DEFAULT NULL, type_id INT DEFAULT NULL, asm_id INT DEFAULT NULL, name VARCHAR(60) NOT NULL, phone VARCHAR(60) DEFAULT NULL, fax VARCHAR(60) DEFAULT NULL, hidden TINYINT(1) NOT NULL, approved TINYINT(1) NOT NULL, url VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, legacy_id INT NOT NULL, address_street1 VARCHAR(255) DEFAULT NULL, address_street2 VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(20) DEFAULT NULL, address_city VARCHAR(50) DEFAULT NULL, address_town VARCHAR(50) DEFAULT NULL, address_state VARCHAR(50) DEFAULT NULL, address_country VARCHAR(2) DEFAULT NULL, legacy_address TEXT DEFAULT "", UNIQUE INDEX UNIQ_62534E215E237E06 (name), INDEX IDX_62534E21F8B9D183 (parent_customer_id), INDEX IDX_62534E21C54C8C93 (type_id), INDEX IDX_62534E219C54D4BF (asm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E21F8B9D183 FOREIGN KEY (parent_customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E21C54C8C93 FOREIGN KEY (type_id) REFERENCES customer_types (id)');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E219C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_CUSTOMER_CREATE"), ("FEATURE_CUSTOMER_EDIT")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_CREATE"
                          AND user_group.name IN("SUPERUSER", "GG_SALES","GG_SALES_AGENTS","GG_SUPPORT","GG_ADMIN","ROLE_ASM","ROLE_SA","SALES_CUST_ADMIN","ROLE_SPM")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CUSTOMER_EDIT"
                          AND user_group.name IN ("SUPERUSER", "ROLE_ASM", "ROLE_EVP", "ROLE_SA", "GG_ADMIN", "ROLE_SPM","ROLE_CSD")');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E21C54C8C93');
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E21F8B9D183');
        $this->addSql('DROP TABLE customer_types');
        $this->addSql('DROP TABLE customers');
    }
}
