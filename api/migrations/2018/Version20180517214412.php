<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180517214412 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE dms (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', owner_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', title VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', subject VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', description TEXT NOT NULL COMMENT \'(DC2Type:string)\', language VARCHAR(32) NOT NULL COMMENT \'(DC2Type:string)\', legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_A9C08F827E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dms ADD CONSTRAINT FK_A9C08F827E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE dms');
    }
}
