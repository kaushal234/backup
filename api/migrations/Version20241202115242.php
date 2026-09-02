<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241202115242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add PDI writing access to PSE and PSA';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_PRE_DELIVERY_INSPECTION_WRITE"
                          AND user_group.name IN ("ROLE_PSE", "ROLE_PSA")');
    }

    public function down(Schema $schema): void
    {
    }
}
