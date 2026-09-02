<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260729043643 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'give FEATURE_POWER_BI_REPORT_UPDATE to new position MIS Data Analyst';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_POWER_BI_REPORT_UPDATE', [
            'ROLE_MISDA',
            'SUPERUSER',
        ]);
    }

    public function down(Schema $schema): void
    {
    }
}
