<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610131025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC link with SPR done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spare_parts_requests_toc ADD technician_on_call_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE spare_parts_requests_toc ADD CONSTRAINT FK_5C3E7598EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('CREATE INDEX IDX_5C3E7598EC02A7D0 ON spare_parts_requests_toc (technician_on_call_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spare_parts_requests_toc DROP FOREIGN KEY FK_5C3E7598EC02A7D0');
        $this->addSql('DROP INDEX IDX_5C3E7598EC02A7D0 ON spare_parts_requests_toc');
        $this->addSql('ALTER TABLE spare_parts_requests_toc DROP technician_on_call_id');
    }
}
