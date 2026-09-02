<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612101738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC - add original_symptoms, original_root_cause, original_solution done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call ADD original_symptoms LONGTEXT DEFAULT NULL, ADD original_root_cause LONGTEXT DEFAULT NULL, ADD original_solution LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call DROP COLUMN original_symptoms, DROP COLUMN original_root_cause, DROP COLUMN original_solution');
    }
}
