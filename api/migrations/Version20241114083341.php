<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241114083341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add specification project';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE access (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, location_property VARCHAR(255) DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_6692B54FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email_access (id INT NOT NULL, recipient_email_id INT DEFAULT NULL, copy_email_id INT DEFAULT NULL, INDEX IDX_7BF3F8A4462A90CD (recipient_email_id), INDEX IDX_7BF3F8A4720E6FCD (copy_email_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification_access (id INT NOT NULL, role_to_notify_id INT DEFAULT NULL, INDEX IDX_C2CED509700F9C51 (role_to_notify_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_access (id INT NOT NULL, user_story_id INT DEFAULT NULL, INDEX IDX_AD4FCAE54AD0A436 (user_story_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE specification (id INT AUTO_INCREMENT NOT NULL, module_id INT NOT NULL, status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_E3F1A9AAFC2B591 (module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_story (id INT AUTO_INCREMENT NOT NULL, specification_id INT DEFAULT NULL, category VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, people_properties LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', jira_issue_number VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_994FF60908E2FFE (specification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_story_emails (id INT AUTO_INCREMENT NOT NULL, user_story_id INT DEFAULT NULL, object VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, people_properties LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', follower TINYINT(1) NOT NULL, INDEX IDX_834E26A24AD0A436 (user_story_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_story_files (id INT NOT NULL, user_story_id INT DEFAULT NULL, INDEX IDX_736E296F4AD0A436 (user_story_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_story_notifications (id INT AUTO_INCREMENT NOT NULL, user_story_id INT DEFAULT NULL, message VARCHAR(255) NOT NULL, follower TINYINT(1) NOT NULL, INDEX IDX_6AF774944AD0A436 (user_story_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE access ADD CONSTRAINT FK_6692B54FE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE email_access ADD CONSTRAINT FK_7BF3F8A4462A90CD FOREIGN KEY (recipient_email_id) REFERENCES user_story_emails (id)');
        $this->addSql('ALTER TABLE email_access ADD CONSTRAINT FK_7BF3F8A4720E6FCD FOREIGN KEY (copy_email_id) REFERENCES user_story_emails (id)');
        $this->addSql('ALTER TABLE email_access ADD CONSTRAINT FK_7BF3F8A4BF396750 FOREIGN KEY (id) REFERENCES access (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification_access ADD CONSTRAINT FK_C2CED509700F9C51 FOREIGN KEY (role_to_notify_id) REFERENCES user_story_notifications (id)');
        $this->addSql('ALTER TABLE notification_access ADD CONSTRAINT FK_C2CED509BF396750 FOREIGN KEY (id) REFERENCES access (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_access ADD CONSTRAINT FK_AD4FCAE54AD0A436 FOREIGN KEY (user_story_id) REFERENCES user_story (id)');
        $this->addSql('ALTER TABLE role_access ADD CONSTRAINT FK_AD4FCAE5BF396750 FOREIGN KEY (id) REFERENCES access (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE specification ADD CONSTRAINT FK_E3F1A9AAFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE user_story ADD CONSTRAINT FK_994FF60908E2FFE FOREIGN KEY (specification_id) REFERENCES specification (id)');
        $this->addSql('ALTER TABLE user_story_emails ADD CONSTRAINT FK_834E26A24AD0A436 FOREIGN KEY (user_story_id) REFERENCES user_story (id)');
        $this->addSql('ALTER TABLE user_story_files ADD CONSTRAINT FK_736E296F4AD0A436 FOREIGN KEY (user_story_id) REFERENCES user_story (id)');
        $this->addSql('ALTER TABLE user_story_files ADD CONSTRAINT FK_736E296FBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_story_notifications ADD CONSTRAINT FK_6AF774944AD0A436 FOREIGN KEY (user_story_id) REFERENCES user_story (id)');
        $this->addSql('ALTER TABLE trouble_ticket ADD is_add_to_user_stories TINYINT(1) NOT NULL');

        $this->addSql("INSERT IGNORE INTO feature (name)
                           VALUES ('FEATURE_SPECIFICATION_CREATE'),
                                  ('FEATURE_SPECIFICATION_UPDATE_STATUS'),
                                  ('FEATURE_USER_STORY_CREATE'),
                                  ('FEATURE_USER_STORY_DELETE'),
                                  ('FEATURE_USER_STORY_UPDATE_STATUS'),
                                  ('FEATURE_TROUBLE_TICKET_ADD_USER_STORY')
                     ");

        $this->insertFeatureGroup('FEATURE_SPECIFICATION_CREATE', ['GG_MIS']);
        $this->insertFeatureGroup('FEATURE_SPECIFICATION_UPDATE_STATUS', ['GG_MIS']);
        $this->insertFeatureGroup('FEATURE_USER_STORY_CREATE', ['GG_MIS']);
        $this->insertFeatureGroup('FEATURE_USER_STORY_DELETE', ['GG_MIS']);
        $this->insertFeatureGroup('FEATURE_USER_STORY_UPDATE_STATUS', ['GG_MIS']);
        $this->insertFeatureGroup('FEATURE_TROUBLE_TICKET_ADD_USER_STORY', ['GG_MIS']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access DROP FOREIGN KEY FK_6692B54FE54D947');
        $this->addSql('ALTER TABLE email_access DROP FOREIGN KEY FK_7BF3F8A4462A90CD');
        $this->addSql('ALTER TABLE email_access DROP FOREIGN KEY FK_7BF3F8A4720E6FCD');
        $this->addSql('ALTER TABLE email_access DROP FOREIGN KEY FK_7BF3F8A4BF396750');
        $this->addSql('ALTER TABLE notification_access DROP FOREIGN KEY FK_C2CED509700F9C51');
        $this->addSql('ALTER TABLE notification_access DROP FOREIGN KEY FK_C2CED509BF396750');
        $this->addSql('ALTER TABLE role_access DROP FOREIGN KEY FK_AD4FCAE54AD0A436');
        $this->addSql('ALTER TABLE role_access DROP FOREIGN KEY FK_AD4FCAE5BF396750');
        $this->addSql('ALTER TABLE specification DROP FOREIGN KEY FK_E3F1A9AAFC2B591');
        $this->addSql('ALTER TABLE user_story DROP FOREIGN KEY FK_994FF60908E2FFE');
        $this->addSql('ALTER TABLE user_story_emails DROP FOREIGN KEY FK_834E26A24AD0A436');
        $this->addSql('ALTER TABLE user_story_files DROP FOREIGN KEY FK_736E296F4AD0A436');
        $this->addSql('ALTER TABLE user_story_files DROP FOREIGN KEY FK_736E296FBF396750');
        $this->addSql('ALTER TABLE user_story_notifications DROP FOREIGN KEY FK_6AF774944AD0A436');
        $this->addSql('DROP TABLE access');
        $this->addSql('DROP TABLE email_access');
        $this->addSql('DROP TABLE notification_access');
        $this->addSql('DROP TABLE role_access');
        $this->addSql('DROP TABLE specification');
        $this->addSql('DROP TABLE user_story');
        $this->addSql('DROP TABLE user_story_emails');
        $this->addSql('DROP TABLE user_story_files');
        $this->addSql('DROP TABLE user_story_notifications');
        $this->addSql('ALTER TABLE trouble_ticket DROP is_add_to_user_stories');
    }

    /**
     * Link feature to a group.
     */
    private function insertFeatureGroup(string $feature, array $groups): void
    {
        foreach ($groups as $group) {
            $sql = 'INSERT IGNORE INTO feature_group (group_id, feature_id)
            SELECT user_group.id, feature.id
            FROM user_group
            JOIN feature ON user_group.name = :group AND feature.name = :feature';

            $parameters = [
                'group' => $group,
                'feature' => $feature,
            ];

            $this->addSql($sql, $parameters);
        }
    }
}
