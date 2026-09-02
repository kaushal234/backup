<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250801074256 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add features for task';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_TASK_WRITE', ['ROLE_CIO', 'GG_MIS', 'ROLE_MISM']);
        $this->insertFeatureGroup('FEATURE_TASK_TRANSFER', ['ROLE_CIO', 'GG_MIS', 'ROLE_MISM']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
