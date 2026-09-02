<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241113140134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add field Jira Project ID on MIS application';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application ADD jira_project_id INT NOT NULL');
        // Dev Project
        $this->addSql('UPDATE application SET jira_project_id=10026');
        // ERP Project
        $this->addSql('UPDATE application SET jira_project_id=10032 WHERE id = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP jira_project_id');
    }
}
