<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230215104216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 're-enable feature FEATURE_COMMENT_WRITE to intranet user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_COMMENT_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_COMMENT_WRITE"
                          AND user_group.name IN ("ACL_AUTH_INTRANET")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM feature_group WHERE feature_id IN (SELECT feature.id from feature WHERE feature.name = "FEATURE_COMMENT_WRITE")');
        $this->addSql('DELETE FROM feature WHERE feature.name = "FEATURE_COMMENT_WRITE"');
    }
}
