<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20211213042903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add GG_ADMIN to FEATURE_GROUPS_ADMIN';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_GROUPS_ADMIN"
                          AND user_group.name IN ("GG_ADMIN")');
    }
}
