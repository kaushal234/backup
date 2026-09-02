<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230908134334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix permissions for derogation status administration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_DEROGATION_STATUS_ADMIN")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_DEROGATION_STATUS_ADMIN"');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEROGATION_STATUS_REOPEN")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEROGATION_STATUS_CLOSE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_STATUS_CLOSE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "GG_QUALITY")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_STATUS_REOPEN"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "GG_QUALITY", "ROLE_PM")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
