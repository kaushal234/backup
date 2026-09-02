<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250102101033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new template notification for third party app.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // 157 = Module 3PA
        $this->addSql("INSERT INTO notification_templates (module_id, name, text) VALUES (157, 'third_party_app_update_tasks_opened', 'You are MOO or Administrator of the 3rd party application %s,<br />
Some update tasks are opened and need your review.<br />')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM notification_templates WHERE module_id = 157 AND name = "third_party_app_update_tasks_opened"');
    }
}
