<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260513115524 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add FEATURE_VWC_FILE_CHANGE_VISIBILITY to QA team';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_VWC_FILE_CHANGE_VISIBILITY', ['ROLE_QAM', 'ROLE_QA', 'ROLE_QE'], false);
    }

    public function down(Schema $schema): void
    {
    }
}
