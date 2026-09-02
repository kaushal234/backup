<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260625152721 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'TOC - Add permission to edit';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_TECHNICIAN_ON_CALL_EDIT', [
            'ROLE_PSM',
            'ROLE_PSE',
            'ROLE_PSA',
            'ROLE_COO',
            'GG_SUPPORT',
        ], false);
    }

    public function down(Schema $schema): void
    {
    }
}
