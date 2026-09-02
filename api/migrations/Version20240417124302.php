<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240417124302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase baan customer number in sales order.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_orders CHANGE baan_customer_number baan_customer_number VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_orders CHANGE baan_customer_number baan_customer_number VARCHAR(6) DEFAULT NULL');
    }
}
