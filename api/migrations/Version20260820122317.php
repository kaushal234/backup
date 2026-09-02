<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260820122317 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add feature FEATURE_CUSTOMER_SERVICE_RECORD_FINANCIAL_STEP to SA and SAM to close a CSR';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_CUSTOMER_SERVICE_RECORD_FINANCIAL_STEP', ['ROLE_SA', 'ROLE_SAM']);
    }

    public function down(Schema $schema): void
    {
    }
}
