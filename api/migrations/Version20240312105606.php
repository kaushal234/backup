<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240312105606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow QAM to access and update supplier rankings.';
    }

    public function up(Schema $schema): void
    {
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_READ', ['ROLE_QAM']);
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_UPDATE', ['ROLE_QAM']);
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_CLASSIFICATION_READ', ['ROLE_QAM']);
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_READ', ['ROLE_QAM']);
        $this->insertFeatureGroup('FEATURE_SUPPLIER_RANKING_CRITERIA_READ', ['ROLE_QAM']);
    }

    /**
     * Link feature to a group.
     */
    public function insertFeatureGroup(string $feature, array $groups)
    {
        foreach ($groups as $group) {
            $this->addSql("INSERT IGNORE INTO feature_group (group_id, feature_id)
                           SELECT user_group.id, feature.id
                           FROM user_group, feature
                           WHERE feature.name = '$feature'
                              AND user_group.name IN ('$group')");
        }
    }

    public function down(Schema $schema): void
    {
    }
}
