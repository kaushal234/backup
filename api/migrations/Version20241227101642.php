<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241227101642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Splits access to ESR planning in Read and Write accesses';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE feature SET name = "FEATURE_PLANNING_DAILY_LIMIT_WRITE" WHERE name = "FEATURE_PLANNING_DAILY_LIMIT"');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PLANNING_DAILY_LIMIT_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_PLANNING_DAILY_LIMIT_READ"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_PSM",
            "ROLE_PSE",
            "ROLE_PSA",
            "ROLE_ASM",
            "ROLE_COO",
            "ROLE_TCOO",
            "ROLE_RCEO",
            "ROLE_EVP"
          )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
