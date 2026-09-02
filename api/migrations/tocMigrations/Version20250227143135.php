<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227143135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC add relation between TechnicianOnCall and CustomerServiceRecord done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_service_record ADD technician_on_call_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_service_record ADD CONSTRAINT FK_3C9EAE6DEC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('CREATE INDEX IDX_3C9EAE6DEC02A7D0 ON customer_service_record (technician_on_call_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_service_record DROP FOREIGN KEY FK_3C9EAE6DEC02A7D0');
        $this->addSql('DROP INDEX IDX_3C9EAE6DEC02A7D0 ON customer_service_record');
        $this->addSql('ALTER TABLE customer_service_record DROP technician_on_call_id');
    }
}
