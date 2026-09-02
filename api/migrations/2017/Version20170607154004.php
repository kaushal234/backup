<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170607154004 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spq_quotations ADD delivery_address_baan_id VARCHAR(3) DEFAULT NULL, ADD billing_address_baan_id VARCHAR(3) DEFAULT NULL, ADD delivery_name VARCHAR(35) NOT NULL, ADD delivery_address_extra VARCHAR(30) NOT NULL, ADD delivery_zip VARCHAR(10) NOT NULL, ADD delivery_city VARCHAR(30) NOT NULL, ADD delivery_city_extra VARCHAR(30) NOT NULL, ADD delivery_country VARCHAR(3) NOT NULL, ADD billing_name VARCHAR(35) NOT NULL, ADD billing_address VARCHAR(30) NOT NULL, ADD billing_address_extra VARCHAR(30) NOT NULL, ADD billing_zip VARCHAR(10) NOT NULL, ADD billing_city VARCHAR(30) NOT NULL, ADD billing_city_extra VARCHAR(30) NOT NULL, ADD billing_country VARCHAR(3) NOT NULL, DROP invoicing_address, CHANGE delivery_address delivery_address VARCHAR(30) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spq_quotations ADD invoicing_address TEXT NOT NULL COLLATE utf8_unicode_ci, DROP delivery_address_baan_id, DROP billing_address_baan_id, DROP delivery_name, DROP delivery_address_extra, DROP delivery_zip, DROP delivery_city, DROP delivery_city_extra, DROP delivery_country, DROP billing_name, DROP billing_address, DROP billing_address_extra, DROP billing_zip, DROP billing_city, DROP billing_city_extra, DROP billing_country, CHANGE delivery_address delivery_address TEXT NOT NULL COLLATE utf8_unicode_ci');
    }
}
