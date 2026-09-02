<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260515072435 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'add role spm to FEATURE_NCR_VENDOR_WARRANTY_CLAIM_CREATE';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_NCR_VENDOR_WARRANTY_CLAIM_CREATE', ['ROLE_SPM']);
    }

    public function down(Schema $schema): void
    {
    }
}
