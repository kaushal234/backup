<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128155733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add contact in sales orders';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sales_orders ADD contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sales_orders ADD CONSTRAINT FK_C7DBAFE6E7A1254A FOREIGN KEY (contact_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_C7DBAFE6E7A1254A ON sales_orders (contact_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sales_orders DROP FOREIGN KEY FK_C7DBAFE6E7A1254A');
        $this->addSql('DROP INDEX IDX_C7DBAFE6E7A1254A ON sales_orders');
        $this->addSql('ALTER TABLE sales_orders DROP contact_id');
    }
}
