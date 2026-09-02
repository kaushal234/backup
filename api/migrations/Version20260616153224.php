<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260616153224 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add access restriction for change log edition';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_CHANGE_LOG_EDIT', ['SUPERUSER', 'ROLE_DEV']);
    }

    public function down(Schema $schema): void
    {
    }
}
