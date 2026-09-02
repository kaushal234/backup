<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625094101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC Double-write done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spare_parts_requests_toc CHANGE technician_on_call_id technician_on_call_id INT NOT NULL');
        $this->addSql('ALTER TABLE technician_on_call ADD legacy_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spare_parts_requests_toc CHANGE technician_on_call_id technician_on_call_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE technician_on_call DROP legacy_id');
    }
}
