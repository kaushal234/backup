<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250902132728 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add FEATURE_CUSTOMER_EDIT to ROLE_COO';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_CUSTOMER_EDIT', ['ROLE_COO']);
    }

    public function down(Schema $schema): void
    {
    }
}
