<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20240820113847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow legal manager to see ranking entirely';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_READ"
          AND user_group.name in ("ROLE_LM")'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SUPPLIER_RANKING_CLASSIFICATION_READ"
          AND user_group.name in ("ROLE_LM")'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SUPPLIER_RANKING_CRITERIA_READ"
          AND user_group.name in ("ROLE_LM")'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_VENDOR_USER_WRITE"
          AND user_group.name in ("ROLE_LM")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
