<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250919123040 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add CRAB writing access to ROLE_ENG';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_CRAB_EDIT', ['ROLE_ENG']);
    }

    public function down(Schema $schema): void
    {
    }
}
