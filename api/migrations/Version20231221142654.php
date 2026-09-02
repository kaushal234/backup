<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231221142654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Manage FAQ : remove for role_qa, add to gg_quality';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name IN('FEATURE_FAQ_PLAN_APPROVAL')
                        AND user_group.name = 'GG_QUALITY'");

        $this->addSql("DELETE feature_group FROM feature_group
            LEFT JOIN feature on feature_group.feature_id = feature.id
            LEFT JOIN user_group on feature_group.group_id = user_group.id
            WHERE user_group.name IN ('ROLE_QA')
            AND feature.name = 'FEATURE_FAQ_PLAN_APPROVAL'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
