<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250929142347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new feature to manage module notification';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE modules ADD notify_operational_owner TINYINT(1) DEFAULT 1 NOT NULL, ADD notify_key_user TINYINT(1) DEFAULT 1 NOT NULL');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MODULES_NOTIFICATION")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MODULES_NOTIFICATION"
          AND user_group.name in ("ROLE_MISM")'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE modules DROP notify_operational_owner, DROP notify_key_user');
    }
}
