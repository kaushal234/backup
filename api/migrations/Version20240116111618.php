<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240116111618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Sales Organisation Service to Equipment Record';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_records ADD sales_organisation_service_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A922F5EE4C FOREIGN KEY (sales_organisation_service_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_EAE697A922F5EE4C ON equipment_records (sales_organisation_service_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_records DROP FOREIGN KEY FK_EAE697A922F5EE4C');
        $this->addSql('DROP INDEX IDX_EAE697A922F5EE4C ON equipment_records');
        $this->addSql('ALTER TABLE equipment_records DROP sales_organisation_service_id');
    }
}
