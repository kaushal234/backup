<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250129194801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC Airport done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call ADD airport_id INT NOT NULL');

        // Just for avoid error for existing TOC on local and staging database. Should not be used for final production migration
        $this->addSql('UPDATE technician_on_call SET airport_id = 1');

        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('CREATE INDEX IDX_3BD0B5C6289F53C8 ON technician_on_call (airport_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6289F53C8');
        $this->addSql('DROP INDEX IDX_3BD0B5C6289F53C8 ON technician_on_call');
        $this->addSql('ALTER TABLE technician_on_call DROP airport_id');
    }
}
