<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619120101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC add customer done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call ADD customer_id INT NOT NULL');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C69395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('CREATE INDEX IDX_3BD0B5C69395C3F3 ON technician_on_call (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C69395C3F3');
        $this->addSql('DROP INDEX IDX_3BD0B5C69395C3F3 ON technician_on_call');
        $this->addSql('ALTER TABLE technician_on_call DROP customer_id');
    }
}
