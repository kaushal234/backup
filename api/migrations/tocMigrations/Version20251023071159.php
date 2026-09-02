<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251023071159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC Add Jira Tracteasy Issue Key done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call ADD jira_tracteasy_issue_key VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call DROP jira_tracteasy_issue_key');
    }
}
