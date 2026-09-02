<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211119164527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add properties "greenTagDate" and "projectNumber" to EquipmentRecord entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records ADD green_tag_date DATETIME DEFAULT NULL, ADD project_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records DROP green_tag_date, DROP project_number');
    }
}
