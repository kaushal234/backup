<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171212145701 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_EXTRANET_USER_IMPERSONATE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_EXTRANET_USER_IMPERSONATE"
                        AND user_group.name in (
                          "SUPERUSER",
                          "GG_ADMIN",
                          "GG_SALES",
                          "GG_PARTS",
                          "GG_SERVICE")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
