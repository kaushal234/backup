<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160916073316 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE module_migration_steps (id INT AUTO_INCREMENT NOT NULL, short_desc VARCHAR(255) NOT NULL, full_desc LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE modules (id INT AUTO_INCREMENT NOT NULL, operational_owner_id INT NOT NULL, mis_owner_id INT NOT NULL, name VARCHAR(4) NOT NULL, short_description VARCHAR(255) NOT NULL, full_description LONGTEXT DEFAULT NULL, dms_procedure_id INT DEFAULT NULL, dms_help_id INT DEFAULT NULL, legacy_loc INT NOT NULL, migration_current_step_id INT DEFAULT NULL, migration_estimated_hours INT NOT NULL, migrated TINYINT(1) NOT NULL, legacy_id INT NOT NULL, UNIQUE INDEX UNIQ_2EB743D75E237E06 (name), INDEX IDX_2EB743D7AA2FB378 (operational_owner_id), INDEX IDX_2EB743D74D3A0D98 (mis_owner_id), INDEX IDX_2EB743D7C8AD84A (migration_current_step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE module_dependencies (module_id INT NOT NULL, module_required_id INT NOT NULL, INDEX IDX_825CE87FAFC2B591 (module_id), INDEX IDX_825CE87F5316F6ED (module_required_id), PRIMARY KEY(module_id, module_required_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D7AA2FB378 FOREIGN KEY (operational_owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D74D3A0D98 FOREIGN KEY (mis_owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D7C8AD84A FOREIGN KEY (migration_current_step_id) REFERENCES module_migration_steps (id)');
        $this->addSql('ALTER TABLE module_dependencies ADD CONSTRAINT FK_825CE87FAFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE module_dependencies ADD CONSTRAINT FK_825CE87F5316F6ED FOREIGN KEY (module_required_id) REFERENCES modules (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MODULE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_MODULE_WRITE"
                          AND user_group.name = "SUPERUSER"');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_MODULE_WRITE"
                          AND user_group.name = "GG_MIS"');

        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('First reflexion', 'Read the related DMS, meet the MOO, learn how it used to be to know what it should be in the new version');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Legacy DB Analysis', 'Start thinking about how resources were stored in legacy database, make a diagram exposing their tables, their relations, their collations, discuss with another developer about how accurate this schema was');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('New DB Conception', 'Design the new resource storage schema, make a diagram of the new structure, validate this structure with either another developer, Jean-Paul or the MOO (or all of them)');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Doctrine Entities', 'Write entities : ORM, validations assertions, double-write annotations, app-specific (loggable...)');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Doctrine Migrations', 'Generate migrations : app/console doctrine:migrations:diff (think about adding the insert statements of permissions)');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Legacy Data Import', 'Write import command(s) : pay attention to encoding issues, conversions (1/0, ''Y'', ''N'' to boolean), pay attention to sanitation of strings (helpers are here to help), don''t forget to add your specific command(s) to the main legacy:import:all command');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('API Platfom config', 'Write services declarations related to your resources : ALL routes must be explicitly declared with their related permissions, pay attention to Deletion Voters');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Backend Testing', 'Testing : write Behat scenarios, write fixtures');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Postman', 'Update Postman json collection by exporting your new routes to the file in the repository');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Routing', 'Document old routing and new routing');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Frontend Controller(s)', 'Write all Controllers and empty Actions with appropriates @Route annotations and annotate them with @todo to let you know you still have to work on it. ');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Route catchers', 'Write all legacy routes catchers');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Actions / Forms', 'Write actions and forms');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Templating', 'Create every templates, html / css / js...');");
        $this->addSql("INSERT INTO module_migration_steps (short_desc, full_desc) VALUES ('Frontend Testing', 'Write tests, record guzzle calls, replay');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE module_dependencies DROP FOREIGN KEY FK_825CE87FAFC2B591');
        $this->addSql('ALTER TABLE module_dependencies DROP FOREIGN KEY FK_825CE87F5316F6ED');
        $this->addSql('DROP TABLE module_migration_steps');
        $this->addSql('DROP TABLE modules');
        $this->addSql('DROP TABLE module_dependencies');
    }
}
