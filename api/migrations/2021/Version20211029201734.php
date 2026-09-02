<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211029201734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make component column nullable (since it kind of is in the legacy, some values are just empty strings)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_serials CHANGE component_id component_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE equipment_serials CHANGE component_id component_id INT NOT NULL');
    }
}
