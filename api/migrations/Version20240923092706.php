<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240923092706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add third party app, extension of module.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE third_party_app_whitelist (extended_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_6CA75A87AEA48C43 (extended_id), INDEX IDX_6CA75A873147C936 (people_id), PRIMARY KEY(extended_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_app_blacklist (extended_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_9CB69166AEA48C43 (extended_id), INDEX IDX_9CB691663147C936 (people_id), PRIMARY KEY(extended_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_app_account_review (id INT AUTO_INCREMENT NOT NULL, third_party_app_id INT DEFAULT NULL, created_by INT DEFAULT NULL, created_at DATETIME NOT NULL, count_admin_users INT DEFAULT NULL, admin_accounts_confirmed TINYINT(1) NOT NULL, count_app_users INT DEFAULT NULL, count_start_members INT DEFAULT NULL, count_end_members INT DEFAULT NULL, count_disabled_accounts INT DEFAULT NULL, disabled_accounts_comment LONGTEXT DEFAULT NULL, count_enabled_accounts INT DEFAULT NULL, enabled_accounts_comment LONGTEXT DEFAULT NULL, user_accounts_confirmed TINYINT(1) NOT NULL, INDEX IDX_D4BBC94E8561029A (third_party_app_id), INDEX IDX_D4BBC94EDE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_app_account_review_files (id INT NOT NULL, account_review_id INT DEFAULT NULL, INDEX IDX_1DA919041C544EC0 (account_review_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_app_business_unit_position (id INT AUTO_INCREMENT NOT NULL, third_party_app_id INT DEFAULT NULL, position_id INT DEFAULT NULL, business_unit_id INT DEFAULT NULL, INDEX IDX_B58265548561029A (third_party_app_id), INDEX IDX_B5826554DD842E46 (position_id), INDEX IDX_B5826554A58ECB40 (business_unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_app_member (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, third_party_app_id INT DEFAULT NULL, admin TINYINT(1) NOT NULL, INDEX IDX_9366F33FA76ED395 (user_id), INDEX IDX_9366F33F8561029A (third_party_app_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_app_security_level (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_app_security_review (id INT AUTO_INCREMENT NOT NULL, third_party_app_id INT DEFAULT NULL, created_by INT DEFAULT NULL, security_level_id INT DEFAULT NULL, created_at DATETIME NOT NULL, security_level_comment LONGTEXT DEFAULT NULL, is_password_policy_applied TINYINT(1) NOT NULL, is_mfaadmin_applied TINYINT(1) NOT NULL, is_mfauser_applied TINYINT(1) NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_654990C18561029A (third_party_app_id), INDEX IDX_654990C1DE12AB56 (created_by), INDEX IDX_654990C13CDACC75 (security_level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_app_security_review_files (id INT NOT NULL, security_review_id INT DEFAULT NULL, INDEX IDX_2FBF58B856904EE4 (security_review_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE third_party_app_update_task (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, third_party_app_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, demand_type VARCHAR(15) NOT NULL, origin_type VARCHAR(255) DEFAULT NULL, task_id INT DEFAULT NULL, done TINYINT(1) NOT NULL, confirmed TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_862B28A8A76ED395 (user_id), INDEX IDX_862B28A88561029A (third_party_app_id), INDEX IDX_862B28A8DE12AB56 (created_by), INDEX IDX_862B28A816FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE third_party_app_whitelist ADD CONSTRAINT FK_6CA75A87AEA48C43 FOREIGN KEY (extended_id) REFERENCES modules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE third_party_app_whitelist ADD CONSTRAINT FK_6CA75A873147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE third_party_app_blacklist ADD CONSTRAINT FK_9CB69166AEA48C43 FOREIGN KEY (extended_id) REFERENCES modules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE third_party_app_blacklist ADD CONSTRAINT FK_9CB691663147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE third_party_app_account_review ADD CONSTRAINT FK_D4BBC94E8561029A FOREIGN KEY (third_party_app_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE third_party_app_account_review ADD CONSTRAINT FK_D4BBC94EDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE third_party_app_account_review_files ADD CONSTRAINT FK_1DA919041C544EC0 FOREIGN KEY (account_review_id) REFERENCES third_party_app_account_review (id)');
        $this->addSql('ALTER TABLE third_party_app_account_review_files ADD CONSTRAINT FK_1DA91904BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE third_party_app_business_unit_position ADD CONSTRAINT FK_B58265548561029A FOREIGN KEY (third_party_app_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE third_party_app_business_unit_position ADD CONSTRAINT FK_B5826554DD842E46 FOREIGN KEY (position_id) REFERENCES directory_position (id)');
        $this->addSql('ALTER TABLE third_party_app_business_unit_position ADD CONSTRAINT FK_B5826554A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('ALTER TABLE third_party_app_member ADD CONSTRAINT FK_9366F33FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE third_party_app_member ADD CONSTRAINT FK_9366F33F8561029A FOREIGN KEY (third_party_app_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE third_party_app_security_review ADD CONSTRAINT FK_654990C18561029A FOREIGN KEY (third_party_app_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE third_party_app_security_review ADD CONSTRAINT FK_654990C1DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE third_party_app_security_review ADD CONSTRAINT FK_654990C13CDACC75 FOREIGN KEY (security_level_id) REFERENCES third_party_app_security_level (id)');
        $this->addSql('ALTER TABLE third_party_app_security_review_files ADD CONSTRAINT FK_2FBF58B856904EE4 FOREIGN KEY (security_review_id) REFERENCES third_party_app_security_review (id)');
        $this->addSql('ALTER TABLE third_party_app_security_review_files ADD CONSTRAINT FK_2FBF58B8BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE third_party_app_update_task ADD CONSTRAINT FK_862B28A8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE third_party_app_update_task ADD CONSTRAINT FK_862B28A88561029A FOREIGN KEY (third_party_app_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE third_party_app_update_task ADD CONSTRAINT FK_862B28A8DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE third_party_app_update_task ADD CONSTRAINT FK_862B28A816FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modules ADD main_admin_id INT DEFAULT NULL, ADD security_level_id INT DEFAULT NULL, ADD discr VARCHAR(255) NOT NULL, ADD sso TINYINT(1) DEFAULT NULL, ADD mfa_user TINYINT(1) DEFAULT NULL, ADD mfa_admin TINYINT(1) DEFAULT NULL, ADD security_review_frequency SMALLINT DEFAULT NULL, ADD security_review_date_start DATETIME DEFAULT NULL, ADD account_review_frequency SMALLINT DEFAULT NULL, ADD account_review_date_start DATETIME DEFAULT NULL, ADD availability_classification SMALLINT DEFAULT NULL, ADD integrity_classification SMALLINT DEFAULT NULL, ADD confidentiality_classification SMALLINT DEFAULT NULL, ADD last_account_review_task_id INT DEFAULT NULL, ADD last_security_review_task_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D7BD099365 FOREIGN KEY (main_admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D73CDACC75 FOREIGN KEY (security_level_id) REFERENCES third_party_app_security_level (id)');
        $this->addSql('CREATE INDEX IDX_2EB743D7BD099365 ON modules (main_admin_id)');
        $this->addSql('CREATE INDEX IDX_2EB743D73CDACC75 ON modules (security_level_id)');

        $this->addSql("UPDATE modules SET discr='module' WHERE 1");

        $this->addSql("INSERT INTO third_party_app_security_level (name) VALUES ('Iso27')");
        $this->addSql("INSERT INTO third_party_app_security_level (name) VALUES ('Soc2T2')");
        $this->addSql("INSERT INTO third_party_app_security_level (name) VALUES ('CSA-STAR')");
        $this->addSql("INSERT INTO third_party_app_security_level (name) VALUES ('Self Assessed')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE modules DROP FOREIGN KEY FK_2EB743D73CDACC75');
        $this->addSql('ALTER TABLE third_party_app_whitelist DROP FOREIGN KEY FK_6CA75A87AEA48C43');
        $this->addSql('ALTER TABLE third_party_app_whitelist DROP FOREIGN KEY FK_6CA75A873147C936');
        $this->addSql('ALTER TABLE third_party_app_blacklist DROP FOREIGN KEY FK_9CB69166AEA48C43');
        $this->addSql('ALTER TABLE third_party_app_blacklist DROP FOREIGN KEY FK_9CB691663147C936');
        $this->addSql('ALTER TABLE third_party_app_account_review DROP FOREIGN KEY FK_D4BBC94E8561029A');
        $this->addSql('ALTER TABLE third_party_app_account_review DROP FOREIGN KEY FK_D4BBC94EDE12AB56');
        $this->addSql('ALTER TABLE third_party_app_account_review_files DROP FOREIGN KEY FK_1DA919041C544EC0');
        $this->addSql('ALTER TABLE third_party_app_account_review_files DROP FOREIGN KEY FK_1DA91904BF396750');
        $this->addSql('ALTER TABLE third_party_app_business_unit_position DROP FOREIGN KEY FK_B58265548561029A');
        $this->addSql('ALTER TABLE third_party_app_business_unit_position DROP FOREIGN KEY FK_B5826554DD842E46');
        $this->addSql('ALTER TABLE third_party_app_business_unit_position DROP FOREIGN KEY FK_B5826554A58ECB40');
        $this->addSql('ALTER TABLE third_party_app_member DROP FOREIGN KEY FK_9366F33FA76ED395');
        $this->addSql('ALTER TABLE third_party_app_member DROP FOREIGN KEY FK_9366F33F8561029A');
        $this->addSql('ALTER TABLE third_party_app_security_review DROP FOREIGN KEY FK_654990C18561029A');
        $this->addSql('ALTER TABLE third_party_app_security_review DROP FOREIGN KEY FK_654990C1DE12AB56');
        $this->addSql('ALTER TABLE third_party_app_security_review DROP FOREIGN KEY FK_654990C13CDACC75');
        $this->addSql('ALTER TABLE third_party_app_security_review_files DROP FOREIGN KEY FK_2FBF58B856904EE4');
        $this->addSql('ALTER TABLE third_party_app_security_review_files DROP FOREIGN KEY FK_2FBF58B8BF396750');
        $this->addSql('ALTER TABLE third_party_app_update_task DROP FOREIGN KEY FK_862B28A8A76ED395');
        $this->addSql('ALTER TABLE third_party_app_update_task DROP FOREIGN KEY FK_862B28A88561029A');
        $this->addSql('ALTER TABLE third_party_app_update_task DROP FOREIGN KEY FK_862B28A8DE12AB56');
        $this->addSql('ALTER TABLE third_party_app_update_task DROP FOREIGN KEY FK_862B28A816FE72E1');
        $this->addSql('DROP TABLE third_party_app_whitelist');
        $this->addSql('DROP TABLE third_party_app_blacklist');
        $this->addSql('DROP TABLE third_party_app_account_review');
        $this->addSql('DROP TABLE third_party_app_account_review_files');
        $this->addSql('DROP TABLE third_party_app_business_unit_position');
        $this->addSql('DROP TABLE third_party_app_member');
        $this->addSql('DROP TABLE third_party_app_security_level');
        $this->addSql('DROP TABLE third_party_app_security_review');
        $this->addSql('DROP TABLE third_party_app_security_review_files');
        $this->addSql('DROP TABLE third_party_app_update_task');
        $this->addSql('ALTER TABLE modules DROP FOREIGN KEY FK_2EB743D7BD099365');
        $this->addSql('DROP INDEX IDX_2EB743D7BD099365 ON modules');
        $this->addSql('DROP INDEX IDX_2EB743D73CDACC75 ON modules');
        $this->addSql('ALTER TABLE modules DROP main_admin_id, DROP security_level_id, DROP discr, DROP sso, DROP mfa_user, DROP mfa_admin, DROP security_review_frequency, DROP security_review_date_start, DROP account_review_frequency, DROP account_review_date_start, DROP availability_classification, DROP integrity_classification, DROP confidentiality_classification, DROP last_account_review_task_id, DROP last_security_review_task_id');
    }
}
