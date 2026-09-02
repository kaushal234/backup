<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260518134921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add first_estimated_green_tag_date property to equipment_records';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records ADD first_estimated_green_tag_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records DROP first_estimated_green_tag_date');
    }
}
