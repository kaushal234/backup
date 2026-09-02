<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250729100934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC - Add serial number to technician on call done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call ADD serial_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call DROP serial_number');
    }
}
