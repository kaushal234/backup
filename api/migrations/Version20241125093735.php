<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125093735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow QA team to change due date';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_DUE_DATE_ADMIN"
                          AND user_group.name IN ("GG_QUALITY")');
    }

    public function down(Schema $schema): void
    {
    }
}
