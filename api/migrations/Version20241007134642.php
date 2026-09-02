<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20241007134642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow ROLE_CFO to see PowerBI AES report';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_POWERBI_AES_READ"
                          AND user_group.name IN ("ROLE_CFO")');
    }

    public function down(Schema $schema): void
    {
    }
}
