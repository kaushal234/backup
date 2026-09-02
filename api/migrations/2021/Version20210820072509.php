<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820072509 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE extranet_user_group ADD public TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('INSERT IGNORE INTO extranet_user_group (name,description,public) VALUES ( "ROLE_STAGING","Access to staging environment", false)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_EXTRANET_USER_GROUP_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_EXTRANET_USER_GROUP_EDIT"
                          AND user_group.name IN ("SUPERUSER")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE extranet_user_group DROP public');
    }
}
