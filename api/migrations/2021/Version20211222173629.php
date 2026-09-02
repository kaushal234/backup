<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211222173629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add signal code on serial component';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_serial_components ADD signal_code VARCHAR(3) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_serial_components DROP signal_code');
    }
}
