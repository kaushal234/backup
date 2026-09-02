<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200909085330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE equipment_records ADD sales_organisation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A9E8E5F9D1 FOREIGN KEY (sales_organisation_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_EAE697A9E8E5F9D1 ON equipment_records (sales_organisation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE equipment_records DROP FOREIGN KEY FK_EAE697A9E8E5F9D1');
        $this->addSql('DROP INDEX IDX_EAE697A9E8E5F9D1 ON equipment_records');
        $this->addSql('ALTER TABLE equipment_records DROP sales_organisation_id');
    }
}
