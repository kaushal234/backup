<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260602095456 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Updates permissions for ROLE_LGM in contracts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_ACCESS") and group_id = 340');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_ACCESS") and group_id = 340');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_BU_ACCESS") and group_id = 340');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_BU_ACCESS") and group_id = 340');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_ACCESS") and group_id = 340');
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_BU_READ_ACCESS") and group_id = 340');

        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_CATEGORY_VENDORS_CONTRACT_BU_ACCESS") and group_id = 340');
        $this->addSql('DELETE from feature WHERE feature.name = "FEATURE_CATEGORY_VENDORS_CONTRACT_BU_ACCESS"');

        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_ACCESS', ['ROLE_LGM'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_CAR_CONTRACT_ACCESS', ['ROLE_LGM'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_ACCESS', ['ROLE_LGM'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_ACCESS', ['ROLE_LGM'], false);
        $this->insertFeatureGroup('FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_ACCESS', ['ROLE_LGM'], false);
        $this->insertFeatureGroup('FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_READ_ACCESS', ['ROLE_LGM'], false);
        $this->insertFeatureGroup('FEATURE_CATEGORY_VENDORS_CONTRACT_ACCESS', ['ROLE_LGM'], false);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
