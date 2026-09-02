<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260528125640 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add properties for auto escalation on TTS';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trouble_ticket ADD auto_escalated TINYINT(1) DEFAULT 0 NOT NULL, ADD auto_escalated_at DATE DEFAULT NULL');

        $this->insertFeatureGroup('FEATURE_TROUBLE_TICKET_ADMIN_CLOSE', ['ROLE_GIA', 'ROLE_RSSI', 'ROLE_CIO']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trouble_ticket DROP auto_escalated, DROP auto_escalated_at');
    }
}
