<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251119162454 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'add feature to read merge request from gitlab';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_READ_GITLAB_MERGE_REQUEST', ['ROLE_DEV', 'ROLE_MISM']);
    }

    public function down(Schema $schema): void
    {
    }
}
