<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220816132237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new feature for the parts dashboard and set default authorized groups.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_PARTS_DASHBOARD_VIEW")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_PARTS_DASHBOARD_VIEW"
                        AND user_group.name IN ("ROLE_CMO", "SUPERUSER", "GG_ADMIN", "GG_PARTS", "GG_SUPPORT", "GG_PUR", "GG_SERVICE", "GG_BOOST", "GG_PARTS_AGENTS", "ROLE_RME", "GG_ENG", "ROLE_FC", "ROLE_ASM")');
    }

    public function down(Schema $schema): void
    {
    }
}
