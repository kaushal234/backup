<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428120333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add property password_policy_applied to module entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE modules ADD password_policy_applied TINYINT(1) DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE modules DROP password_policy_applied');
    }
}
