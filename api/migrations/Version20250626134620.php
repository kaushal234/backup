<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626134620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add notification template for waiting Trouble Ticket';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO notification_templates (module_id, name, text) VALUES (33, 'trouble_ticket_await_user', 'This TTS is waiting for your answer')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
