<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260522104923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added delivery_early column to the sales_order_lines table to recognize if the unit is marked with early delivery or not';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sales_order_lines ADD delivered_early BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sales_order_lines DROP COLUMN delivered_early');
    }
}
