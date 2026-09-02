<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305152042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TTS update last commented at to datetime';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trouble_ticket CHANGE last_commented_at last_commented_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trouble_ticket CHANGE last_commented_at last_commented_at DATE DEFAULT NULL');
    }
}
