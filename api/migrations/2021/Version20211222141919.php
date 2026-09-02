<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211222141919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add field options description on ER';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records ADD options_description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_records DROP options_description');
    }
}
