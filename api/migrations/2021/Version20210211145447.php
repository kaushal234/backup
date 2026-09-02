<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20210211145447 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SURVEY_DELETE_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_SURVEY_DELETE_ADMIN"
			AND user_group.name in ("SUPERUSER")'
        );
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SURVEY_EDIT_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_SURVEY_EDIT_ADMIN"
			AND user_group.name in ("SUPERUSER")'
        );
    }
}
