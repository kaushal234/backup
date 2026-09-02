<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151116081601 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE activity (id BIGINT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, resource VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, public TINYINT(1) NOT NULL, legacy_id INT NOT NULL, discr VARCHAR(255) NOT NULL, action VARCHAR(255) DEFAULT NULL, change_set LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', message LONGTEXT DEFAULT NULL, INDEX IDX_AC74095AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE acronym CHANGE legacy_id legacy_id INT NOT NULL');
        $this->addSql('ALTER TABLE directory_position CHANGE legacy_id legacy_id INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE legacy_id legacy_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE activity');
        $this->addSql('ALTER TABLE acronym CHANGE legacy_id legacy_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE directory_position CHANGE legacy_id legacy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE legacy_id legacy_id INT DEFAULT NULL');
    }
}
