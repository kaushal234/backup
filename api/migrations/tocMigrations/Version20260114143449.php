<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260114143449 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'TOC Allow AST, CSS and CSTL to suspend a TOC done and cp';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_TECHNICIAN_ON_CALL_SUSPENDED', ['ROLE_AST', 'ROLE_CSA'], false);
    }

    public function down(Schema $schema): void
    {
    }
}
