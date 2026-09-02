<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200626192955 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) 	VALUES("FEATURE_PRODUCTION_RESOURCE_PLANNING_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_PRODUCTION_RESOURCE_PLANNING_WRITE"
			AND user_group.name = "SUPERUSER"'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) 	VALUES("FEATURE_MATERIAL_REQUIREMENT_PLANNING_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_MATERIAL_REQUIREMENT_PLANNING_WRITE"
			AND user_group.name = "SUPERUSER"'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) 	VALUES("FEATURE_WORK_ORDER_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_WORK_ORDER_WRITE"
			AND user_group.name = "SUPERUSER"'
        );
    }

    public function down(Schema $schema): void
    {
        // nothing to do
    }
}
