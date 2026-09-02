<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250805065915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new feature for new derogation status ARCHIVED';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEROGATION_STATUS_ARCHIVE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_STATUS_ARCHIVE"
                          AND user_group.name IN ("SUPERUSER", "ROLE_QAM")');
    }

    public function down(Schema $schema): void
    {
    }
}
