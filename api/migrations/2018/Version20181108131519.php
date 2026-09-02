<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181108131519 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE first_article_qualifications ADD plan_approval_status VARCHAR(255) NOT NULL');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FAQ_PLAN_APPROVAL")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_FAQ_PLAN_APPROVAL"
                        AND user_group.name in (
                            "SUPERUSER",
                            "ROLE_CMO",
                            "ROLE_QAM"
                        )');
        $this->addSql('UPDATE first_article_qualifications SET plan_approval_status = "NOT_APPROVED_YET"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE first_article_qualifications DROP plan_approval_status');
    }
}
