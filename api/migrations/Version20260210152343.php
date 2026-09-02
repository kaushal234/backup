<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210152343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add field for SOL synchronization';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_order_lines ADD delivery_penalties TINYINT(1) DEFAULT 0 NOT NULL, ADD delivery_penalties_conditions LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_order_lines DROP delivery_penalties, DROP delivery_penalties_conditions');
    }
}
