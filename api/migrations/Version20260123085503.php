<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123085503 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Contract features';
    }

    public function up(Schema $schema): void
    {
        // Building contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_ACCESS', ['ROLE_LGS'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_ACCESS', ['ROLE_LGM'], false);

        // Car contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_CAR_CONTRACT_ACCESS', ['ROLE_LGS'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_ACCESS', ['ROLE_LGM'], false);

        // Worker compensation contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_ACCESS', ['ROLE_LGS'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_ACCESS', ['ROLE_LGM'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_EDIT_ACCESS', ['ROLE_HRM'], false);

        // General liability contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_BU_ACCESS', ['ROLE_LGM'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_ACCESS', ['ROLE_LGS'], false);

        // Aero liability contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_BU_ACCESS', ['ROLE_LGM'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_ACCESS', ['ROLE_LGS'], false);

        // D&O contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_DO_CONTRACT_ACCESS', ['ROLE_LGS'], false);

        // Bank contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_BANK_CONTRACT_BU_ACCESS', ['ROLE_GTM'], false);
        $this->insertFeatureGroup('FEATURE_CATEGORY_BANK_CONTRACT_READ_ACCESS', ['ROLE_LGS'], false);

        // IT contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_IT_CONTRACT_READ_ACCESS', ['ROLE_LGS'], false);

        // Customer contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_CUSTOMER_CONTRACT_READ_ACCESS', ['ROLE_LGS'], false);

        // M&A contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_MA_CONTRACT_EDIT_ACCESS', ['ROLE_LGS'], false);

        // Real estate contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_READ_ACCESS', ['ROLE_LGS'], false);
        $this->insertFeatureGroup('FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_BU_READ_ACCESS', ['ROLE_LGM'], false);

        // Vendors contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_VENDORS_CONTRACT_READ_ACCESS', ['ROLE_LGS'], false);
        $this->insertFeatureGroup('FEATURE_CATEGORY_VENDORS_CONTRACT_BU_ACCESS', ['ROLE_LGM'], false);

        // Interco contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_INTERCO_CONTRACT_ACCESS', ['ROLE_LGS'], false);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
