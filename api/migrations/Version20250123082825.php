<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250123082825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Power Bi 51 & 52 access to PSE & PSA';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_FINANCE_BOOKING_READ"
          AND user_group.name in (
            "ROLE_PSE",
            "ROLE_PSA"
          )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
