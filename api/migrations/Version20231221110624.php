<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231221110624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow QAT to manage FAQ (Give access to the features FEATURE_FAQ_WRITE and FEATURE_FAQ_PLAN_WRITE to QAT)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name IN("FEATURE_FAQ_PLAN_APPROVAL")
                        AND user_group.name  = "ROLE_QA"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
