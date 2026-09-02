<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210721154711 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DOWNLOAD_DIRECTORY")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DOWNLOAD_DIRECTORY"
                          AND user_group.name IN ("SUPERUSER", "GG_HR", "ROLE_CFO", "ROLE_GCFO", "ROLE_GTCD", "ROLE_LM")');
    }

    public function down(Schema $schema): void
    {
    }
}
