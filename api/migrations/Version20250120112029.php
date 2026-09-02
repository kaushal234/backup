<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20250120112029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'allow PSE and PSA to handle ESR (to assist PSM) and production and SAM to see planning';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_ESR_WRITE"
                          AND user_group.name IN ("ROLE_PSA", "ROLE_PSE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_PLANNING_DAILY_LIMIT_READ"
                          AND user_group.name IN ("GG_PRODUCTION", "ROLE_SAM")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
