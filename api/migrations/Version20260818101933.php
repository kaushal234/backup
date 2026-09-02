<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260818101933 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add feature to re-open a TOC';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_REOPEN_TECHNICIAN_ON_CALL', ['ROLE_CSM', 'ROLE_CSD', 'SUPERUSER']);
    }

    public function down(Schema $schema): void
    {
    }
}
