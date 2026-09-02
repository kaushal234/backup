<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909151359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Support Team entity and features';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE support_teams (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE premises ADD support_team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE premises ADD CONSTRAINT FK_4A01730A9459D052 FOREIGN KEY (support_team_id) REFERENCES support_teams (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_4A01730A9459D052 ON premises (support_team_id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_CREATE_SUPPORT_TEAM")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_CREATE_SUPPORT_TEAM"
                          AND user_group.name IN ("ROLE_MISM", "ROLE_CIO")');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DELETE_SUPPORT_TEAM")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DELETE_SUPPORT_TEAM"
                          AND user_group.name IN ("ROLE_MISM", "ROLE_CIO")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE premises DROP FOREIGN KEY FK_4A01730A9459D052');
        $this->addSql('DROP TABLE support_teams');
        $this->addSql('DROP INDEX IDX_4A01730A9459D052 ON premises');
        $this->addSql('ALTER TABLE premises DROP support_team_id');
    }
}
