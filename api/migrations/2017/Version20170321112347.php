<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170321112347 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE files (id INT AUTO_INCREMENT NOT NULL, poster_id INT DEFAULT NULL, file_path LONGTEXT NOT NULL, created_at DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, sha VARCHAR(128) NOT NULL, mime_type VARCHAR(255) NOT NULL, extension VARCHAR(8) NOT NULL, size INT NOT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_63540595BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE out_of_tolerance_form_files (id INT NOT NULL, out_of_tolerance_form_id INT DEFAULT NULL, INDEX IDX_27D12CD02046B8D5 (out_of_tolerance_form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE files ADD CONSTRAINT FK_63540595BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE out_of_tolerance_form_files ADD CONSTRAINT FK_27D12CD02046B8D5 FOREIGN KEY (out_of_tolerance_form_id) REFERENCES out_of_tolerance_form (id)');
        $this->addSql('ALTER TABLE out_of_tolerance_form_files ADD CONSTRAINT FK_27D12CD0BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE calibration_log DROP INDEX UNIQ_F68D4792046B8D5, ADD INDEX IDX_F68D4792046B8D5 (out_of_tolerance_form_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE out_of_tolerance_form_files DROP FOREIGN KEY FK_27D12CD0BF396750');
        $this->addSql('DROP TABLE files');
        $this->addSql('DROP TABLE out_of_tolerance_form_files');
        $this->addSql('ALTER TABLE calibration_log DROP INDEX IDX_F68D4792046B8D5, ADD UNIQUE INDEX UNIQ_F68D4792046B8D5 (out_of_tolerance_form_id)');
    }
}
