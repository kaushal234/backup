<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201124143513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a unique index on sector on name + location';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX unique_sector_name_per_location ON sectors (name, location_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX unique_sector_name_per_location ON sectors');
    }
}
