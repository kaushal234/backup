<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227130901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new template notification for derogation.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO notification_templates (module_id, name, text) VALUES (16, 'derogation_decision_update', 'A derogation on this CRAB is assigned to you and requires action on your side')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM notification_templates WHERE module_id = 16 AND name = "derogation_decision_update"');
    }
}
