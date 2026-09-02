<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526065817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow QAM to see part dashboard';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PARTS_DASHBOARD_VIEW")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_PARTS_DASHBOARD_VIEW"
          AND user_group.name in (
            "ROLE_QAM"
          )'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
