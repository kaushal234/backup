<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20181227151050 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name IN("FEATURE_FAQ_WRITE", "FEATURE_FAQ_PLAN_WRITE", "FEATURE_FAQ_DELETE_FILE")
                        AND user_group.name  = "ROLE_QE"');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_FAQ_CREATE"
                        AND user_group.name IN ("ROLE_QE", "ROLE_QAM")');
    }

    public function down(Schema $schema): void
    {
        // TODO: Implement down() method.
    }
}
