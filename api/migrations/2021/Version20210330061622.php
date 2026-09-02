<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20210330061622 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demos DROP approval_date;');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEMO_CREATE"
                          AND user_group.name in ("ROLE_EVP")'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEMO_EDIT"
                          AND user_group.name in ("ROLE_SAM")'
        );
        $this->addSql('DELETE FROM feature_group WHERE feature_id = 63 and group_id = 50');
        $this->addSql('DELETE FROM feature_group WHERE feature_id = 64');
        $this->addSql('DELETE FROM feature_group WHERE feature_id = 74');
        $this->addSql('DELETE FROM feature WHERE name = "FEATURE_DEMO_FILES_DELETE"');
        $this->addSql('DELETE FROM feature WHERE name = "FEATURE_DEMO_ADMIN_EDIT"');
        $this->addSql('UPDATE feature SET name = "FEATURE_DEMO_ADMIN" WHERE name = "FEATURE_DEMO_ADMIN_STATUS"');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEMO_STATUS")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
			SELECT user_group.id, feature.id
			FROM user_group, feature
			WHERE feature.name = "FEATURE_DEMO_STATUS"
			AND user_group.name in ("ROLE_SAM", "SUPERUSER", "ROLE_SA", "ROLE_EVP")'
        );
    }
}
