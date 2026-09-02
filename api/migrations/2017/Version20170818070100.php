<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170818070100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SURVEY_VIEW")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SURVEY_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SURVEY_DELETE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SURVEY_VIEW"
                          AND user_group.name IN (
                          "SUPERUSER",
                          "ROLE_SURVEY_CONTENT_MANAGER")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SURVEY_WRITE"
                          AND user_group.name IN (
                          "SUPERUSER",
                          "ROLE_SURVEY_CONTENT_MANAGER")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SURVEY_DELETE"
                          AND user_group.name IN (
                          "SUPERUSER",
                          "ROLE_SURVEY_CONTENT_MANAGER")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
