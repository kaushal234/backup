<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124075529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add updated_by and updated_at to lead_times';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lead_times ADD updated_by INT DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE lead_times ADD CONSTRAINT FK_7B30230316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7B30230316FE72E1 ON lead_times (updated_by)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lead_times DROP FOREIGN KEY FK_7B30230316FE72E1');
        $this->addSql('DROP INDEX IDX_7B30230316FE72E1 ON lead_times');
        $this->addSql('ALTER TABLE lead_times DROP updated_by, DROP updated_at');
    }
}
