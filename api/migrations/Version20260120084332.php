<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120084332 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add customer relation to Contract entity, add displayedName to Category and subCategory from Contracts, add confidential property on Contracts and add features linked to contract';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contracts_customers (contract_id INT NOT NULL, customer_id INT NOT NULL, INDEX IDX_BA0853052576E0FD (contract_id), INDEX IDX_BA0853059395C3F3 (customer_id), PRIMARY KEY(contract_id, customer_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE contracts_customers ADD CONSTRAINT FK_BA0853052576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id)');
        $this->addSql('ALTER TABLE contracts_customers ADD CONSTRAINT FK_BA0853059395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');

        $this->addSql('ALTER TABLE category ADD displayed_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE contract ADD confidential TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE sub_category ADD displayed_name VARCHAR(255) NOT NULL');

        $this->addSql('UPDATE category SET displayed_name = name WHERE displayed_name IS NULL OR displayed_name = ""');
        $this->addSql('UPDATE sub_category SET displayed_name = name WHERE displayed_name IS NULL OR displayed_name = ""');

        $this->addSql('ALTER TABLE category MODIFY displayed_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sub_category MODIFY displayed_name VARCHAR(255) NOT NULL');

        // Insert features and feature groups
        $this->insertFeatureGroup('FEATURE_FULL_CONTRACT_ACCESS', ['ROLE_CHAIRMAN']);

        // Building contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_ACCESS', ['ROLE_LGS']);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_ACCESS', ['ROLE_LGM', 'ROLE_LCM']);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_EDIT_ACCESS', ['ROLE_CFO']);

        // Car contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_CAR_CONTRACT_ACCESS', ['ROLE_LGS']);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_ACCESS', ['ROLE_LGM', 'ROLE_LCM']);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_EDIT_ACCESS', ['ROLE_CFO']);

        // Worker compensation contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_ACCESS', ['ROLE_LGS']);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_ACCESS', ['ROLE_LGM', 'ROLE_LCM']);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_EDIT_ACCESS', ['ROLE_CFO', 'ROLE_HRM']);

        // General liability contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_BU_ACCESS', ['ROLE_LGM', 'ROLE_LCM']);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_ACCESS', ['ROLE_LGS']);

        // Aero liability contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_BU_ACCESS', ['ROLE_LGM', 'ROLE_LCM']);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_ACCESS', ['ROLE_LGS']);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_ACCESS_BUSINESS_UNIT', ['ROLE_CFO']);

        // D&O contract features
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_DO_CONTRACT_ACCESS', ['ROLE_LGS']);

        // Bank contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_BANK_CONTRACT_BU_ACCESS', ['ROLE_CFO', 'ROLE_GTM']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_BANK_CONTRACT_ACCESS', ['ROLE_GTD']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_BANK_CONTRACT_READ_ACCESS', ['ROLE_LGS']);

        // IT contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_IT_CONTRACT_READ_ACCESS', ['ROLE_LGS']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_IT_CONTRACT_ACCESS', ['ROLE_CIO']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_IT_CONTRACT_BU_ACCESS', ['ROLE_MISM']);

        // Customer contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_CUSTOMER_CONTRACT_READ_ACCESS', ['ROLE_LGS']);

        // M&A contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_MA_CONTRACT_EDIT_ACCESS', ['ROLE_LGS']);

        // Real estate contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_ACCESS', ['ROLE_CFO']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_READ_ACCESS', ['ROLE_LGS']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_BU_READ_ACCESS', ['ROLE_LGM', 'ROLE_LCM', 'ROLE_CFO']);

        // Vendors contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_VENDORS_CONTRACT_READ_ACCESS', ['ROLE_LGS']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_VENDORS_CONTRACT_BU_ACCESS', ['ROLE_LGM']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_VENDORS_CONTRACT_ACCESS', ['ROLE_MLM']);

        // Interco contract features
        $this->insertFeatureGroup('FEATURE_CATEGORY_INTERCO_CONTRACT_ACCESS', ['ROLE_LGS']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_INTERCO_CONTRACT_BU_ACCESS', ['ROLE_CFO']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_ACCESS', ['ROLE_GTD']);
        $this->insertFeatureGroup('FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_BU_ACCESS', ['ROLE_GTM']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts_customers DROP FOREIGN KEY FK_BA0853052576E0FD');
        $this->addSql('ALTER TABLE contracts_customers DROP FOREIGN KEY FK_BA0853059395C3F3');
        $this->addSql('DROP TABLE contracts_customers');
        $this->addSql('ALTER TABLE contract DROP confidential');
        $this->addSql('ALTER TABLE sub_category DROP displayed_name');
        $this->addSql('ALTER TABLE category DROP displayed_name');

        // Delete features
        $features = [
            'FEATURE_FULL_CONTRACT_ACCESS',
            'FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_ACCESS',
            'FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_ACCESS',
            'FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_EDIT_ACCESS',
            'FEATURE_SUB_CATEGORY_CAR_CONTRACT_ACCESS',
            'FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_ACCESS',
            'FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_EDIT_ACCESS',
            'FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_ACCESS',
            'FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_ACCESS',
            'FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_EDIT_ACCESS',
            'FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_BU_ACCESS',
            'FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_ACCESS',
            'FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_BU_ACCESS',
            'FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_ACCESS',
            'FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_ACCESS_BUSINESS_UNIT',
            'FEATURE_SUB_CATEGORY_DO_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_BANK_CONTRACT_BU_ACCESS',
            'FEATURE_CATEGORY_BANK_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_BANK_CONTRACT_READ_ACCESS',
            'FEATURE_CATEGORY_IT_CONTRACT_READ_ACCESS',
            'FEATURE_CATEGORY_IT_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_IT_CONTRACT_BU_ACCESS',
            'FEATURE_CATEGORY_CUSTOMER_CONTRACT_READ_ACCESS',
            'FEATURE_CATEGORY_MA_CONTRACT_EDIT_ACCESS',
            'FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_READ_ACCESS',
            'FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_BU_READ_ACCESS',
            'FEATURE_CATEGORY_VENDORS_CONTRACT_READ_ACCESS',
            'FEATURE_CATEGORY_VENDORS_CONTRACT_BU_ACCESS',
            'FEATURE_CATEGORY_VENDORS_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_INTERCO_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_INTERCO_CONTRACT_BU_ACCESS',
            'FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_ACCESS',
            'FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_BU_ACCESS',
        ];

        foreach ($features as $feature) {
            $this->addSql('DELETE FROM feature_group WHERE feature_id = (SELECT id FROM feature WHERE name = :feature)', ['feature' => $feature]);
            $this->addSql('DELETE FROM feature WHERE name = :feature', ['feature' => $feature]);
        }
    }
}
