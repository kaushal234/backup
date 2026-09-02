<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260819124601 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add ROLE_ASM to feature FEATURE_TECHNICIAN_ON_CALL_SUSPENDED';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_TECHNICIAN_ON_CALL_SUSPENDED', ['ROLE_ASM'], false);
    }

    public function down(Schema $schema): void
    {
    }
}
