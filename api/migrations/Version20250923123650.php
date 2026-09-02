<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923123650 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add features access to Contracts entity';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_READ_CONTRACT', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_UPDATE_CONTRACT', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTRACT', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_DELETE_CONTRACT_FILE', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_READ_CONTRACT_CATEGORY', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_UPDATE_CONTRACT_CATEGORY', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTRACT_CATEGORY', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_DELETE_CONTRACT_CATEGORY', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_READ_CONTRACT_SUB_CATEGORY', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_UPDATE_CONTRACT_SUB_CATEGORY', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTRACT_SUB_CATEGORY', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
        $this->insertFeatureGroup('FEATURE_DELETE_CONTRACT_SUB_CATEGORY', ['ROLE_MISM', 'ROLE_GCH', 'ROLE_LM']);
    }

    public function down(Schema $schema): void
    {
    }
}
