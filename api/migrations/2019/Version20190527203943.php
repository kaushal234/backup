<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190527203943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Link features to intranet groups';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_LINK_ENGINEERING")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_LINK_ENGINEERING"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_EM",
            "ROLE_ES",
            "ROLE_RME"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_LINK_PRODUCTION")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_LINK_PRODUCTION"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_PM",
            "ROLE_MPE",
            "PI_OPERATOR"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_LINK_ADMIN")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_LINK_ADMIN"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_EM",
            "ROLE_ES"
          )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
