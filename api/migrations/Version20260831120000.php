<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260831120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add warranty_end_date and warranty_conditions columns on equipment_records';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records ADD warranty_end_date DATETIME DEFAULT NULL, ADD warranty_conditions LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records DROP warranty_end_date, DROP warranty_conditions');
    }
}
