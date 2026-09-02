<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180905134826 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE shortage_request (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', poster_id INT NOT NULL COMMENT \'(DC2Type:integer)\', parameters LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_2E67AED05BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shortage_request ADD CONSTRAINT FK_2E67AED05BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('CREATE TABLE shortage_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shortage_files ADD CONSTRAINT FK_651889A2BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE shortage_request');
        $this->addSql('DROP TABLE shortage_files');
    }
}
