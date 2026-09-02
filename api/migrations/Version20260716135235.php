<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716135235 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'FEATURE_DEROGATION_DELETE - permission to remove a derogation';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_DEROGATION_DELETE', [
            'ROLE_QAM',
            'GG_QUALITY',
        ]);
    }

    public function down(Schema $schema): void
    {
    }
}
