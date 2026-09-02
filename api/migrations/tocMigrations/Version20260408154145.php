<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260408154145 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'TOC Add FEATURE_TECHNICIAN_ON_CALL_DELETE for ROLE_CSM done and cp';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_TECHNICIAN_ON_CALL_DELETE', ['ROLE_CSM', 'SUPERUSER']);
    }

    public function down(Schema $schema): void
    {
    }
}
