<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260706160249 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Allow GCSM, CSM, SSD and CSS to move TOC to CLOSED status';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_TECHNICIAN_ON_CALL_CLOSED', ['ROLE_CSM', 'ROLE_EVP', 'ROLE_CSA']);
    }

    public function down(Schema $schema): void
    {
    }
}
