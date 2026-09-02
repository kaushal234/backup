<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211222130706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change equipment record project number field type to integer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records CHANGE project_number project_number INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records CHANGE project_number project_number VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
