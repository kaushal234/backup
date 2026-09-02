<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20220124062923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add SUPERUSER, GG_SALES_AGENTS to FEATURE_SALES_FORECAST_RESTRICTED_EDIT';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SALES_FORECAST_RESTRICTED_EDIT"
                          AND user_group.name IN ("SUPERUSER", "GG_SALES_AGENTS")');
    }
}
