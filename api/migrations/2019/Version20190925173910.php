<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190925173910 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE modules ADD department_id INT DEFAULT NULL, CHANGE operational_owner_id operational_owner_id INT DEFAULT NULL, CHANGE mis_owner_id mis_owner_id INT DEFAULT NULL, CHANGE migration_current_step_id migration_current_step_id INT DEFAULT NULL, CHANGE dms_procedure_id dms_procedure_id INT DEFAULT NULL, CHANGE dms_help_id dms_help_id INT DEFAULT NULL, CHANGE migrated_at migrated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE modules ADD CONSTRAINT FK_2EB743D7AE80F5DF FOREIGN KEY (department_id) REFERENCES directory_department (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2EB743D7AE80F5DF ON modules (department_id)');
        $this->addSql('ALTER TABLE change_logs CHANGE module_id module_id INT DEFAULT NULL, CHANGE ticket ticket INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE modules DROP FOREIGN KEY FK_2EB743D7AE80F5DF');
        $this->addSql('DROP INDEX IDX_2EB743D7AE80F5DF ON modules');
        $this->addSql('ALTER TABLE modules DROP department_id, CHANGE operational_owner_id operational_owner_id INT DEFAULT NULL, CHANGE mis_owner_id mis_owner_id INT DEFAULT NULL, CHANGE migration_current_step_id migration_current_step_id INT DEFAULT NULL, CHANGE dms_procedure_id dms_procedure_id INT DEFAULT NULL, CHANGE dms_help_id dms_help_id INT DEFAULT NULL, CHANGE migrated_at migrated_at DATETIME DEFAULT \'NULL\'');
    }
}
