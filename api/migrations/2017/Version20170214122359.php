<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170214122359 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_ACRONYM_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_ACRONYM_WRITE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_CEO", "ROLE_COO", "ROLE_CMO")');
    }

    public function down(Schema $schema): void
    {
    }
}
