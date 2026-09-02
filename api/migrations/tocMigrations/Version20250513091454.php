<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513091454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC add quotation required to parts done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_part ADD quotation_required TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call_part DROP quotation_required');
    }
}
