<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180523142746 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEMO_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEMO_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEMO_FILES_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_DEMO_EDIT"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_ASM",
            "ROLE_SA",
            "ROLE_PSM",
            "ROLE_CSM",
            "ROLE_EVP",
            "ROLE_COO"
          )'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_DEMO_CREATE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_ASM"
          )'
        );
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_DEMO_FILES_DELETE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_EVP",
            "ROLE_COO"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
