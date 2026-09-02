<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240123102026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create new TTS module';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX unique_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trouble_ticket (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, assignee_id INT DEFAULT NULL, mis_assignee_id INT DEFAULT NULL, type_id INT NOT NULL, module_id INT DEFAULT NULL, jira_issue_number VARCHAR(255) DEFAULT NULL, short_description TINYTEXT NOT NULL, description LONGTEXT NOT NULL, indice_factor VARCHAR(255) DEFAULT NULL, satisfaction VARCHAR(255) DEFAULT NULL, due_date DATE DEFAULT NULL, created_at DATE NOT NULL, closed_at DATE DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_29D21CE2B03A8386 (created_by_id), INDEX IDX_29D21CE259EC7D60 (assignee_id), INDEX IDX_29D21CE230D25DDA (mis_assignee_id), INDEX IDX_29D21CE2C54C8C93 (type_id), INDEX IDX_29D21CE2AFC2B591 (module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trouble_ticket_ccs (trouble_ticket_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_E7914125428D2F72 (trouble_ticket_id), INDEX IDX_E79141253147C936 (people_id), PRIMARY KEY(trouble_ticket_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trouble_ticket_additional_owners (trouble_ticket_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_FCFB4B27428D2F72 (trouble_ticket_id), INDEX IDX_FCFB4B273147C936 (people_id), PRIMARY KEY(trouble_ticket_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trouble_ticket_files (id INT NOT NULL, trouble_ticket_id INT DEFAULT NULL, INDEX IDX_C95060B2428D2F72 (trouble_ticket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, indice_factor LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_default_assignee (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, module_id INT NOT NULL, default_assignee VARCHAR(255) NOT NULL, INDEX IDX_2D464DF3C54C8C93 (type_id), INDEX IDX_2D464DF3AFC2B591 (module_id), UNIQUE INDEX unique_default_assignee_per_module_per_type (type_id, module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE259EC7D60 FOREIGN KEY (assignee_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE230D25DDA FOREIGN KEY (mis_assignee_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE2C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE2AFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE trouble_ticket_ccs ADD CONSTRAINT FK_E7914125428D2F72 FOREIGN KEY (trouble_ticket_id) REFERENCES trouble_ticket (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trouble_ticket_ccs ADD CONSTRAINT FK_E79141253147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trouble_ticket_additional_owners ADD CONSTRAINT FK_FCFB4B27428D2F72 FOREIGN KEY (trouble_ticket_id) REFERENCES trouble_ticket (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trouble_ticket_additional_owners ADD CONSTRAINT FK_FCFB4B273147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trouble_ticket_files ADD CONSTRAINT FK_C95060B2428D2F72 FOREIGN KEY (trouble_ticket_id) REFERENCES trouble_ticket (id)');
        $this->addSql('ALTER TABLE trouble_ticket_files ADD CONSTRAINT FK_C95060B2BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_default_assignee ADD CONSTRAINT FK_2D464DF3C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE type_default_assignee ADD CONSTRAINT FK_2D464DF3AFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE modules DROP FOREIGN KEY FK_2EB743D74D3A0D98');
        $this->addSql('DROP INDEX IDX_2EB743D74D3A0D98 ON modules');
        $this->addSql('ALTER TABLE modules CHANGE name name VARCHAR(20) NOT NULL, ADD application_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D73E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
        $this->addSql('CREATE INDEX IDX_2EB743D73E030ACD ON modules (application_id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TROUBLE_TICKET_TRANSFER")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TROUBLE_TICKET_COMMENT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TROUBLE_TICKET_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_APPLICATION_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_APPLICATION_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TYPE_DEFAULT_ASSIGNEE_BATCH")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TROUBLE_TICKET_UPLOAD_FILE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TROUBLE_TICKET_DELETE_FILE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TROUBLE_TICKET_STATUS")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_TROUBLE_TICKET_TRANSFER"
          AND user_group.name in ("GG_MIS")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_TROUBLE_TICKET_COMMENT"
          AND user_group.name in ("GG_MIS")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_TROUBLE_TICKET_EDIT"
          AND user_group.name in ("GG_MIS")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_APPLICATION_EDIT"
          AND user_group.name in ("ROLE_MISM")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_APPLICATION_CREATE"
          AND user_group.name in ("ROLE_MISM")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_TYPE_DEFAULT_ASSIGNEE_BATCH"
          AND user_group.name in ("ROLE_MISM")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_TROUBLE_TICKET_UPLOAD_FILE"
          AND user_group.name in ("GG_MIS")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_TROUBLE_TICKET_DELETE_FILE"
          AND user_group.name in ("GG_MIS")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_TROUBLE_TICKET_STATUS"
          AND user_group.name in ("GG_MIS")'
        );

        $this->addSql('INSERT IGNORE INTO type (type, description, indice_factor) VALUES("Incident", "The incident prevents me from proceeding.", "IF 100")');
        $this->addSql('INSERT IGNORE INTO type (type, description, indice_factor) VALUES("Incident", "The incident is related to information security and requires high attention.", "IF 1000")');
        $this->addSql('INSERT IGNORE INTO type (type, description, indice_factor) VALUES("Incident", "The incident is related to information security and do not require immediate incident response.", "IF 1")');
        $this->addSql('INSERT IGNORE INTO type (type, description, indice_factor) VALUES("Incident", "Annoying incident that does not prevent me from proceeding.", "IF 1")');
        $this->addSql('INSERT IGNORE INTO type (type, description, indice_factor) VALUES("Incident", "The incident prevents multiple users from proceeding.", "IF 1000")');

        $this->addSql('INSERT IGNORE INTO type (type, description) VALUES("Request", "I need to get more permissions.")');
        $this->addSql('INSERT IGNORE INTO type (type, description) VALUES("Request", "I require additional training/guidance in order to proceed.")');
        $this->addSql('INSERT IGNORE INTO type (type, description) VALUES("Request", "Propose a new feature/service or an enhancement of an existing feature/service.")');
        $this->addSql('INSERT IGNORE INTO type (type, description) VALUES("Request", "Any other request not listed previously.")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE modules DROP FOREIGN KEY FK_2EB743D73E030ACD');
        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE2B03A8386');
        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE259EC7D60');
        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE230D25DDA');
        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE2C54C8C93');
        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE2AFC2B591');
        $this->addSql('ALTER TABLE trouble_ticket_ccs DROP FOREIGN KEY FK_E7914125428D2F72');
        $this->addSql('ALTER TABLE trouble_ticket_ccs DROP FOREIGN KEY FK_E79141253147C936');
        $this->addSql('ALTER TABLE trouble_ticket_additional_owners DROP FOREIGN KEY FK_FCFB4B27428D2F72');
        $this->addSql('ALTER TABLE trouble_ticket_additional_owners DROP FOREIGN KEY FK_FCFB4B273147C936');
        $this->addSql('ALTER TABLE trouble_ticket_files DROP FOREIGN KEY FK_C95060B2428D2F72');
        $this->addSql('ALTER TABLE trouble_ticket_files DROP FOREIGN KEY FK_C95060B2BF396750');
        $this->addSql('ALTER TABLE type_default_assignee DROP FOREIGN KEY FK_2D464DF3C54C8C93');
        $this->addSql('ALTER TABLE type_default_assignee DROP FOREIGN KEY FK_2D464DF3AFC2B591');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE trouble_ticket');
        $this->addSql('DROP TABLE trouble_ticket_ccs');
        $this->addSql('DROP TABLE trouble_ticket_additional_owners');
        $this->addSql('DROP TABLE trouble_ticket_files');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE type_default_assignee');
        $this->addSql('DROP INDEX IDX_2EB743D73E030ACD ON modules');
        $this->addSql('ALTER TABLE modules CHANGE name name VARCHAR(5) NOT NULL, CHANGE application_id mis_owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D74D3A0D98 FOREIGN KEY (mis_owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2EB743D74D3A0D98 ON modules (mis_owner_id)');
    }
}
