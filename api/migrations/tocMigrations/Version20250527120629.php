<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527120629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC parts improvement done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_part ADD replacement VARCHAR(255) DEFAULT NULL, DROP supplier_replaces, DROP customer_replaces, DROP return_required, DROP quotation_required');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_part ADD supplier_replaces TINYINT(1) NOT NULL, ADD customer_replaces TINYINT(1) NOT NULL, ADD return_required TINYINT(1) NOT NULL, ADD quotation_required TINYINT(1) NOT NULL, DROP replacement');
    }
}
