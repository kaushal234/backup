<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211026081435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add feature to access mentoring properties';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PEOPLE_MENTORING_VIEW")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_PEOPLE_MENTORING_VIEW"
                          AND user_group.name IN ("SUPERUSER", "GG_HR", "GG_EXCOM")');
    }

    public function down(Schema $schema): void
    {
    }
}
