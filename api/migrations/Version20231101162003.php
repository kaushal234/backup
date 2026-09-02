<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20231101162003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow GG_HELPDESK_ADMIN to edit identity of user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_PEOPLE_IDENTITY_UPDATE"
          AND user_group.name in ("GG_HELPDESK_ADMIN")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
