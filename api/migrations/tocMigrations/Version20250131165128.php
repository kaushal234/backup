<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250131165128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC Add Sales Organisation Service done';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call ADD sales_organisation_service_id INT NOT NULL');

        // Just for avoid error for existing TOC on local and staging database. Should not be used for final production migration
        $this->addSql('UPDATE technician_on_call SET sales_organisation_service_id = 7');

        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C622F5EE4C FOREIGN KEY (sales_organisation_service_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_3BD0B5C622F5EE4C ON technician_on_call (sales_organisation_service_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C622F5EE4C');
        $this->addSql('DROP INDEX IDX_3BD0B5C622F5EE4C ON technician_on_call');
        $this->addSql('ALTER TABLE technician_on_call DROP sales_organisation_service_id');
    }
}
