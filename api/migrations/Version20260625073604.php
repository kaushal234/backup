<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260625073604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add delivery date in Equipment Record entity.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_records ADD actual_delivery_date DATETIME DEFAULT NULL, CHANGE state state VARCHAR(50) DEFAULT \'ACTIVE\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_records DROP actual_delivery_date, CHANGE state state VARCHAR(50) NOT NULL');
    }
}
