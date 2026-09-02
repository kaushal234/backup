<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214092225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removed unused feature';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_TROUBLE_TICKET_TRANSFER")');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_TROUBLE_TICKET_TRANSFER"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
