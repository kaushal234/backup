<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171229082641 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customers ADD status VARCHAR(20 ) DEFAULT "PENDING" COMMENT \'(DC2Type:string)\', CHANGE parent_customer_id parent_customer_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE type_id type_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE asm_id asm_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE country_id country_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE phone phone VARCHAR(60) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE fax fax VARCHAR(60) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE url url VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE logo logo VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_street1 address_street1 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_street2 address_street2 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_postal_code address_postal_code VARCHAR(20) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_city address_city VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_town address_town VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE address_state address_state VARCHAR(50) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE deleted_at deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customers DROP status, CHANGE parent_customer_id parent_customer_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE country_id country_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE type_id type_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE asm_id asm_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE phone phone VARCHAR(60) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE fax fax VARCHAR(60) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE url url VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE logo logo VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime)\', CHANGE address_street1 address_street1 VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_street2 address_street2 VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_postal_code address_postal_code VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_city address_city VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_town address_town VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE address_state address_state VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
    }
}
