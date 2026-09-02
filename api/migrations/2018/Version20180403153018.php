<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180403153018 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE change_logs (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', module_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', author_id INT NOT NULL COMMENT \'(DC2Type:integer)\', hash VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', date DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', message VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', ticket INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', UNIQUE INDEX UNIQ_93588FE7D1B862B8 (hash), INDEX IDX_93588FE7AFC2B591 (module_id), INDEX IDX_93588FE7F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE change_logs ADD CONSTRAINT FK_93588FE7AFC2B591 FOREIGN KEY (module_id) REFERENCES modules (id)');
        $this->addSql('ALTER TABLE change_logs ADD CONSTRAINT FK_93588FE7F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE change_logs');
    }
}
