<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610123739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Grant access to shadow function of Vendor Users to ROLE_ES and ROLE_EM';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_VENDOR_USER_IMPERSONATE"
          AND user_group.name in (
            "ROLE_ES",
            "ROLE_EM"
          )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
