<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170612131006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE spq_quotation_lines ADD inventory_unit VARCHAR(3) NOT NULL, ADD weight DOUBLE PRECISION NOT NULL, CHANGE discount discount DOUBLE PRECISION NOT NULL, CHANGE commission commission DOUBLE PRECISION NOT NULL, CHANGE tax tax DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE spq_quotations ADD submitted_at DATETIME DEFAULT NULL, ADD baan_customer_name VARCHAR(50) NOT NULL, ADD delivery_name_extra VARCHAR(30) NOT NULL, ADD billing_name_extra VARCHAR(30) NOT NULL, CHANGE baan_customer_number baan_customer_number VARCHAR(6) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE spq_quotation_lines DROP inventory_unit, DROP weight, CHANGE discount discount NUMERIC(6, 2) NOT NULL, CHANGE commission commission NUMERIC(6, 2) NOT NULL, CHANGE tax tax NUMERIC(6, 2) NOT NULL');
        $this->addSql('ALTER TABLE spq_quotations DROP submitted_at, DROP baan_customer_name, DROP delivery_name_extra, DROP billing_name_extra, CHANGE baan_customer_number baan_customer_number VARCHAR(6) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
