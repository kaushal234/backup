<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220303105117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change EquipmentRecord project type to string';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records CHANGE project_number project_number VARCHAR(9) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records CHANGE project_number project_number INT DEFAULT NULL');
    }
}
