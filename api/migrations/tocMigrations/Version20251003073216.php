<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251003073216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC add Solved AT done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call ADD solved_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call DROP solved_at');
    }
}
