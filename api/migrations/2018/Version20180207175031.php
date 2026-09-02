<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180207175031 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', created_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', discr VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', INDEX IDX_6FBC9426DE12AB56 (created_by), UNIQUE INDEX unique_tag_per_resource_type (name, discr), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tags ADD CONSTRAINT FK_6FBC9426DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tags');
    }
}
