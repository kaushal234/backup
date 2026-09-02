<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251214203154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add last comment property mis_projects';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mis_projects ADD last_commented_at DATETIME DEFAULT NULL, ADD last_comment LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mis_projects DROP last_commented_at, DROP last_comment');
    }
}
