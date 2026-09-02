<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230222090509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add new feature to allow supervisor to end a timekeeping transaction for a given employee';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TIMEKEEPING_CLOSE_TRANSACTION")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_TIMEKEEPING_CLOSE_TRANSACTION"
                          AND user_group.name IN ("SUPERUSER", "ROLE_PS", "ROLE_PM", "ROLE_MPE")');
    }

    public function down(Schema $schema): void
    {
    }
}
