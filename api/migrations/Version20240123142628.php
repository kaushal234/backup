<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240123142628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds files to derogations';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE derogation_file (id INT NOT NULL, derogation_id INT DEFAULT NULL, INDEX IDX_EA38DE19BD69C3F1 (derogation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE derogation_file ADD CONSTRAINT FK_EA38DE19BD69C3F1 FOREIGN KEY (derogation_id) REFERENCES derogation (id)');
        $this->addSql('ALTER TABLE derogation_file ADD CONSTRAINT FK_EA38DE19BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEROGATION_FILE_UPLOAD")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_DEROGATION_FILE_DELETE")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_FILE_UPLOAD"
                          AND user_group.name IN ("SUPERUSER", "ROLE_PM", "ROLE_QAM", "GG_QUALITY", "ROLE_WS", "ROLE_PSM", "ROLE_EM", "ROLE_GL")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_DEROGATION_FILE_DELETE"
                          AND user_group.name IN ("SUPERUSER", "GG_QUALITY", "ROLE_QAM", "ROLE_QA")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE derogation_file DROP FOREIGN KEY FK_EA38DE19BD69C3F1');
        $this->addSql('ALTER TABLE derogation_file DROP FOREIGN KEY FK_EA38DE19BF396750');
        $this->addSql('DROP TABLE derogation_file');
    }
}
