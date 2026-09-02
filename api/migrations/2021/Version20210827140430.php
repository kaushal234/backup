<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210827140430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add FEATURE_DOWNLOAD_DIRECTORY to ROLE_FCG';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
            SELECT user_group.id, feature.id
            FROM user_group, feature
            WHERE feature.name = "FEATURE_DOWNLOAD_DIRECTORY"
            AND user_group.name IN ("ROLE_FCG")');
    }

    public function down(Schema $schema): void
    {
    }
}
