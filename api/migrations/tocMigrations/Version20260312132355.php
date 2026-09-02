<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312132355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC: technician field done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call ADD technician_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6E6C5D496 FOREIGN KEY (technician_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3BD0B5C6E6C5D496 ON technician_on_call (technician_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6E6C5D496');
        $this->addSql('DROP INDEX IDX_3BD0B5C6E6C5D496 ON technician_on_call');
        $this->addSql('ALTER TABLE technician_on_call DROP technician_id');
    }
}
