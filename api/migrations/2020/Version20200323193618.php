<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200323193618 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_JOB_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_JOB_WRITE"
          AND user_group.name in (
            "SUPERUSER",
            "GG_HR"
          )'
        );

        $this->addSql('CREATE TABLE jobs (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, business_unit_id INT NOT NULL, country_id INT NOT NULL, created_at DATETIME NOT NULL, title VARCHAR(100) NOT NULL, experience VARCHAR(100) DEFAULT NULL, diploma VARCHAR(150) DEFAULT NULL, description LONGTEXT NOT NULL, enabled TINYINT(1) NOT NULL, synchronized TINYINT(1) NOT NULL, legacy_id INT NOT NULL, INDEX IDX_A8936DC5B03A8386 (created_by_id), INDEX IDX_A8936DC5A58ECB40 (business_unit_id), INDEX IDX_A8936DC5F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_files (id INT NOT NULL, job_id INT DEFAULT NULL, INDEX IDX_769FD29BE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC5B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC5A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id)');
        $this->addSql('ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC5F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE job_files ADD CONSTRAINT FK_769FD29BE04EA9 FOREIGN KEY (job_id) REFERENCES jobs (id)');
        $this->addSql('ALTER TABLE job_files ADD CONSTRAINT FK_769FD29BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_files DROP FOREIGN KEY FK_769FD29BE04EA9');
        $this->addSql('DROP TABLE jobs');
        $this->addSql('DROP TABLE job_files');
    }
}
