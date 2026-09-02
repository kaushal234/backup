<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171214145630 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_SURVEY_CAMPAIGN_SEND")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_SURVEY_CAMPAIGN_SEND"
                        AND user_group.name in (
                          "SUPERUSER",
                          "ROLE_GCEO",
                          "ROLE_GCOO")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SURVEY_VIEW"
                          AND user_group.name IN (
                          "SUPERUSER",
                          "ROLE_EVP",
                          "ROLE_SAM",
                          "ROLE_ASM",
                          "ROLE_CSM",
                          "ROLE_SPM",
                          "ROLE_CSD",
                          "ROLE_COO",
                          "ROLE_PSM",
                          "ROLE_COO",
                          "ROLE_GCOO",
                          "ROLE_CEO",
                          "ROLE_GCEO"
                        )');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SURVEY_WRITE"
                          AND user_group.name IN (
                          "SUPERUSER",
                          "ROLE_EVP",
                          "ROLE_SAM",
                          "ROLE_CSM",
                          "ROLE_SPM",
                          "ROLE_CSD",
                          "ROLE_COO",
                          "ROLE_PSM",
                          "ROLE_COO",
                          "ROLE_GCOO",
                          "ROLE_CEO",
                          "ROLE_GCEO"
                       )');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_SURVEY_DELETE"
                          AND user_group.name IN (
                          "SUPERUSER",
                          "ROLE_EVP",
                          "ROLE_CSD",
                          "ROLE_COO",
                          "ROLE_COO",
                          "ROLE_GCOO",
                          "ROLE_CEO",
                          "ROLE_GCEO"
                       )');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
