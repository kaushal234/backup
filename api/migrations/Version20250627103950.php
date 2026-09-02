<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250627103950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate MIS Project and Tasks';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE base_task (id INT AUTO_INCREMENT NOT NULL, module_id INT DEFAULT NULL, created_by INT DEFAULT NULL, assignee INT DEFAULT NULL, indice_factor VARCHAR(10) DEFAULT NULL, created_at DATETIME NOT NULL, started_at DATETIME DEFAULT NULL, due_date DATETIME NOT NULL, closed_at DATE DEFAULT NULL, short_description VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, legacy_id INT NOT NULL, status VARCHAR(255) NOT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_C41D15D5AFC2B591 (module_id), INDEX IDX_C41D15D5DE12AB56 (created_by), INDEX IDX_C41D15D57C9DFC0C (assignee), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mis_project_files (id INT NOT NULL, project_id INT DEFAULT NULL, INDEX IDX_30244603166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mis_projects (id INT AUTO_INCREMENT NOT NULL, business_unit_id INT DEFAULT NULL, project_manager_id INT DEFAULT NULL, mis_owner_id INT DEFAULT NULL, module_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, indices_factor VARCHAR(255) NOT NULL, created_at DATE NOT NULL, started_at DATE NOT NULL, confidential TINYINT(1) NOT NULL, conclusion VARCHAR(255) DEFAULT NULL, legacy_id INT NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_6F95DFC5A58ECB40 (business_unit_id), INDEX IDX_6F95DFC560984F51 (project_manager_id), INDEX IDX_6F95DFC54D3A0D98 (mis_owner_id), INDEX IDX_6F95DFC5AFC2B591 (module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mis_project_module_key_users_people (project_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_4262CED5166D1F9C (project_id), INDEX IDX_4262CED53147C936 (people_id), PRIMARY KEY(project_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mis_project_mis_members_people (project_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_8A59124D166D1F9C (project_id), INDEX IDX_8A59124D3147C936 (people_id), PRIMARY KEY(project_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phases (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, number INT NOT NULL, estimated_closure_at DATE DEFAULT NULL, revised_closure_at DATE DEFAULT NULL, estimated_hours INT NOT NULL, revised_estimated_hours INT DEFAULT NULL, INDEX IDX_170969E5166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phase_task (phase_id INT NOT NULL, task_id INT NOT NULL, INDEX IDX_A2F9660B99091188 (phase_id), INDEX IDX_A2F9660B8DB60186 (task_id), PRIMARY KEY(phase_id, task_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_tags (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_tags_xref (project_tag_id INT NOT NULL, project_id INT NOT NULL, INDEX IDX_7A7A5959AD76885B (project_tag_id), INDEX IDX_7A7A5959166D1F9C (project_id), PRIMARY KEY(project_tag_id, project_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT NOT NULL, location_id INT DEFAULT NULL, confidential TINYINT(1) NOT NULL, reference_id INT DEFAULT NULL, escalation_trigger INT NOT NULL, escalation_trigger_unit VARCHAR(10) NOT NULL, last_comment LONGTEXT DEFAULT NULL, close_comment LONGTEXT DEFAULT NULL, INDEX IDX_527EDB2564D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_recipients (task_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_875796B8DB60186 (task_id), INDEX IDX_875796B3147C936 (people_id), PRIMARY KEY(task_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_file (id INT NOT NULL, task_id INT DEFAULT NULL, INDEX IDX_FF2CA26B8DB60186 (task_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE base_task ADD CONSTRAINT FK_C41D15D5AFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE base_task ADD CONSTRAINT FK_C41D15D5DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE base_task ADD CONSTRAINT FK_C41D15D57C9DFC0C FOREIGN KEY (assignee) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mis_project_files ADD CONSTRAINT FK_30244603166D1F9C FOREIGN KEY (project_id) REFERENCES mis_projects (id)');
        $this->addSql('ALTER TABLE mis_project_files ADD CONSTRAINT FK_30244603BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mis_projects ADD CONSTRAINT FK_6F95DFC5A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('ALTER TABLE mis_projects ADD CONSTRAINT FK_6F95DFC560984F51 FOREIGN KEY (project_manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mis_projects ADD CONSTRAINT FK_6F95DFC54D3A0D98 FOREIGN KEY (mis_owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mis_projects ADD CONSTRAINT FK_6F95DFC5AFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE mis_project_module_key_users_people ADD CONSTRAINT FK_4262CED5166D1F9C FOREIGN KEY (project_id) REFERENCES mis_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mis_project_module_key_users_people ADD CONSTRAINT FK_4262CED53147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mis_project_mis_members_people ADD CONSTRAINT FK_8A59124D166D1F9C FOREIGN KEY (project_id) REFERENCES mis_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mis_project_mis_members_people ADD CONSTRAINT FK_8A59124D3147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phases ADD CONSTRAINT FK_170969E5166D1F9C FOREIGN KEY (project_id) REFERENCES mis_projects (id)');
        $this->addSql('ALTER TABLE phase_task ADD CONSTRAINT FK_A2F9660B99091188 FOREIGN KEY (phase_id) REFERENCES phases (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_task ADD CONSTRAINT FK_A2F9660B8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_tags ADD CONSTRAINT FK_562D5C3EBF396750 FOREIGN KEY (id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_tags_xref ADD CONSTRAINT FK_7A7A5959AD76885B FOREIGN KEY (project_tag_id) REFERENCES project_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_tags_xref ADD CONSTRAINT FK_7A7A5959166D1F9C FOREIGN KEY (project_id) REFERENCES mis_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2564D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25BF396750 FOREIGN KEY (id) REFERENCES base_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_recipients ADD CONSTRAINT FK_875796B8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_recipients ADD CONSTRAINT FK_875796B3147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_file ADD CONSTRAINT FK_FF2CA26B8DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE task_file ADD CONSTRAINT FK_FF2CA26BBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE modules ADD front_end_route VARCHAR(255) DEFAULT NULL');

        foreach ($this->connection->fetchAllAssociative('SELECT * FROM trouble_ticket') as $troubleTicket) {
            $sql = 'INSERT INTO base_task (id, created_by, assignee, module_id, short_description, description, indice_factor, due_date, created_at, closed_at, status, legacy_id, discr) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $this->addSql($sql, [
                $troubleTicket['id'],
                $troubleTicket['created_by_id'],
                $troubleTicket['assignee_id'],
                $troubleTicket['module_id'],
                addslashes($troubleTicket['short_description']),
                addslashes($troubleTicket['description']),
                $troubleTicket['indice_factor'],
                $troubleTicket['due_date'],
                $troubleTicket['created_at'],
                $troubleTicket['closed_at'],
                $troubleTicket['status'],
                $troubleTicket['legacy_id'],
                'trouble_ticket',
            ]);
        }

        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE2AFC2B591');
        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE2B03A8386');
        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE259EC7D60');
        $this->addSql('DROP INDEX IDX_29D21CE2B03A8386 ON trouble_ticket');
        $this->addSql('DROP INDEX IDX_29D21CE2AFC2B591 ON trouble_ticket');
        $this->addSql('DROP INDEX IDX_29D21CE259EC7D60 ON trouble_ticket');
        $this->addSql('ALTER TABLE trouble_ticket DROP created_by_id, DROP assignee_id, DROP module_id, DROP short_description, DROP description, DROP indice_factor, DROP due_date, DROP created_at, DROP closed_at, DROP status, DROP legacy_id, CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE2BF396750 FOREIGN KEY (id) REFERENCES base_task (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MIS_PROJECT_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MIS_PROJECT_FILE_UPLOAD")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MIS_PROJECT_FILE_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MIS_PROJECT_CHANGE_VISIBILITY")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MIS_PROJECT_CIO_EDIT")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MIS_PROJECT_READ_CONFIDENTIAL")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MIS_PROJECT_CLOSE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MIS_PROJECT_FILE_DOWNLOAD")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MIS_PROJECT_WRITE"
          AND user_group.name in ("GG_MIS", "ROLE_CIO")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MIS_PROJECT_FILE_UPLOAD"
          AND user_group.name in ("GG_MIS", "ROLE_CIO")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MIS_PROJECT_CHANGE_VISIBILITY"
          AND user_group.name in ("GG_MIS", "ROLE_CIO")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MIS_PROJECT_CIO_EDIT"
          AND user_group.name in ("ROLE_CIO")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MIS_PROJECT_READ_CONFIDENTIAL"
          AND user_group.name in ("ROLE_CIO")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MIS_PROJECT_CLOSE"
          AND user_group.name in ("ROLE_CIO")'
        );

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_MIS_PROJECT_FILE_DOWNLOAD"
          AND user_group.name in ("ROLE_CIO")'
        );

        $this->addSql("INSERT INTO notification_templates (module_id, name, text) VALUES (31, 'task_assigned', 'This task has been assigned to you')");
        $this->addSql("INSERT INTO notification_templates (module_id, name, text) VALUES (31, 'task_closed', 'This task has been closed')");
        $this->addSql("INSERT INTO notification_templates (module_id, name, text) VALUES (31, 'task_rescheduled', 'This task has been rescheduled to')");

        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("NETWORK",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("DESK/OFFICE PHONE",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("EMAIL",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("SOFTWARE",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("HARDWARE",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("OTHER",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("BACKUP",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("MIS",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("WEBSITE, INTRANET",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("WEBSITE, eQuotes",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("WEBSITE, Customer EXTRANET",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("WEBSITE, eVendor",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("WEBSITE, SHOPFLOOR",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("WEBSITE",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("ERP",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("WEBSITE, DMS",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("CELL PHONE",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("SUPPLIES",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("SHOPFLOOR TABLET",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("SECURITY",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("Solidworks/PDMworks/SeeElec",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("PURCHASING REQUEST",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("SECURITY - Critical",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("KELIO",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("ERP - EAM/SUN",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("LINK FMS",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("COMMUNICATION",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("WEBSITE, Customer ePARTS",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("Birst Reports",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("ERP - Infor LN",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("CPQ - Infor",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("Factory Track",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("AGILE",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("OPPORTUNITY",NOW(),"project")');
        $this->addSql('INSERT IGNORE INTO tags (name, created_at, discr) VALUES ("ONBOARDING",NOW(),"project")');

        $this->addSql('INSERT IGNORE INTO project_tags (id) SELECT id FROM tags WHERE discr="project"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE2BF396750');
        $this->addSql('ALTER TABLE base_task DROP FOREIGN KEY FK_C41D15D5AFC2B591');
        $this->addSql('ALTER TABLE base_task DROP FOREIGN KEY FK_C41D15D5DE12AB56');
        $this->addSql('ALTER TABLE base_task DROP FOREIGN KEY FK_C41D15D57C9DFC0C');
        $this->addSql('ALTER TABLE mis_project_files DROP FOREIGN KEY FK_30244603166D1F9C');
        $this->addSql('ALTER TABLE mis_project_files DROP FOREIGN KEY FK_30244603BF396750');
        $this->addSql('ALTER TABLE mis_projects DROP FOREIGN KEY FK_6F95DFC5A58ECB40');
        $this->addSql('ALTER TABLE mis_projects DROP FOREIGN KEY FK_6F95DFC560984F51');
        $this->addSql('ALTER TABLE mis_projects DROP FOREIGN KEY FK_6F95DFC54D3A0D98');
        $this->addSql('ALTER TABLE mis_projects DROP FOREIGN KEY FK_6F95DFC5AFC2B591');
        $this->addSql('ALTER TABLE mis_project_module_key_users_people DROP FOREIGN KEY FK_4262CED5166D1F9C');
        $this->addSql('ALTER TABLE mis_project_module_key_users_people DROP FOREIGN KEY FK_4262CED53147C936');
        $this->addSql('ALTER TABLE mis_project_mis_members_people DROP FOREIGN KEY FK_8A59124D166D1F9C');
        $this->addSql('ALTER TABLE mis_project_mis_members_people DROP FOREIGN KEY FK_8A59124D3147C936');
        $this->addSql('ALTER TABLE phases DROP FOREIGN KEY FK_170969E5166D1F9C');
        $this->addSql('ALTER TABLE phase_task DROP FOREIGN KEY FK_A2F9660B99091188');
        $this->addSql('ALTER TABLE phase_task DROP FOREIGN KEY FK_A2F9660B8DB60186');
        $this->addSql('ALTER TABLE project_tags DROP FOREIGN KEY FK_562D5C3EBF396750');
        $this->addSql('ALTER TABLE project_tags_xref DROP FOREIGN KEY FK_7A7A5959AD76885B');
        $this->addSql('ALTER TABLE project_tags_xref DROP FOREIGN KEY FK_7A7A5959166D1F9C');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2564D218E');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25BF396750');
        $this->addSql('ALTER TABLE task_recipients DROP FOREIGN KEY FK_875796B8DB60186');
        $this->addSql('ALTER TABLE task_recipients DROP FOREIGN KEY FK_875796B3147C936');
        $this->addSql('ALTER TABLE task_file DROP FOREIGN KEY FK_FF2CA26B8DB60186');
        $this->addSql('ALTER TABLE task_file DROP FOREIGN KEY FK_FF2CA26BBF396750');
        $this->addSql('DROP TABLE base_task');
        $this->addSql('DROP TABLE mis_project_files');
        $this->addSql('DROP TABLE mis_projects');
        $this->addSql('DROP TABLE mis_project_module_key_users_people');
        $this->addSql('DROP TABLE mis_project_mis_members_people');
        $this->addSql('DROP TABLE phases');
        $this->addSql('DROP TABLE phase_task');
        $this->addSql('DROP TABLE project_tags');
        $this->addSql('DROP TABLE project_tags_xref');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE task_recipients');
        $this->addSql('DROP TABLE task_file');
        $this->addSql('ALTER TABLE modules DROP front_end_route');
        $this->addSql('ALTER TABLE trouble_ticket ADD created_by_id INT DEFAULT NULL, ADD assignee_id INT DEFAULT NULL, ADD module_id INT DEFAULT NULL, ADD short_description TINYTEXT NOT NULL, ADD description LONGTEXT NOT NULL, ADD indice_factor VARCHAR(255) DEFAULT NULL, ADD due_date DATE DEFAULT NULL, ADD created_at DATE NOT NULL, ADD closed_at DATE DEFAULT NULL, ADD status VARCHAR(255) NOT NULL, ADD legacy_id INT NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE2AFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE259EC7D60 FOREIGN KEY (assignee_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_29D21CE2B03A8386 ON trouble_ticket (created_by_id)');
        $this->addSql('CREATE INDEX IDX_29D21CE2AFC2B591 ON trouble_ticket (module_id)');
        $this->addSql('CREATE INDEX IDX_29D21CE259EC7D60 ON trouble_ticket (assignee_id)');
    }
}
