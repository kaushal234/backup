<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224123525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add state and status to EquipmentRecord';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE equipment_records ADD state VARCHAR(50) NOT NULL DEFAULT 'ACTIVE', ADD status VARCHAR(50) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_records DROP state, DROP status');
    }
}
