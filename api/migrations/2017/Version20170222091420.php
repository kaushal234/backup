<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add People features.
 */
class Version20170222091420 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PEOPLE_UPDATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PEOPLE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_PEOPLE_WRITE"
                          AND user_group.name IN ("ACL_AUTH_INTRANET")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_PEOPLE_UPDATE"
                          AND user_group.name IN ("SUPERUSER", "GG_HR", "GG_MIS")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
