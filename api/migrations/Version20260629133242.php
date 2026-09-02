<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629133242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add third_party_ref to technician_on_call';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call ADD third_party_ref VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call DROP third_party_ref');
    }
}
