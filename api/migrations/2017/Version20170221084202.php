<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170221084202 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_ACL_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_ACL_WRITE"
                          AND user_group.name = "SUPERUSER"');
    }

    public function down(Schema $schema): void
    {
    }
}
