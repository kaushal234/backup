<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20240814143252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow legal manager to see and update ranking for corruption matter and shadow vendor user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SUPPLIER_RANKING_READ_ALL"
          AND user_group.name in ("ROLE_LM")'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_SUPPLIER_RANKING_UPDATE"
          AND user_group.name in ("ROLE_LM")'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_VENDOR_USER_IMPERSONATE"
          AND user_group.name in ("ROLE_LM")'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
