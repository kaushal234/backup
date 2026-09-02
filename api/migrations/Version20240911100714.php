<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240911100714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'allow quality department members to create manuals';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_MANUAL_ADMIN"
                          AND user_group.name IN ("ROLE_QE", "ROLE_QA", "ROLE_QAM")');
    }

    public function down(Schema $schema): void
    {
    }
}
