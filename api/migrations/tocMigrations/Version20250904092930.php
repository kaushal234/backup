<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250904092930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC parts comment not required done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_part DROP serial_number, CHANGE comment comment VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_part ADD serial_number VARCHAR(255) DEFAULT NULL, CHANGE comment comment VARCHAR(255) NOT NULL');
    }
}
