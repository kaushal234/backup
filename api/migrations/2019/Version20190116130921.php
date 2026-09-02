<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190116130921 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user ADD extranet_user_linked_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64910F532ED FOREIGN KEY (extranet_user_linked_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64910F532ED ON user (extranet_user_linked_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64910F532ED');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B56089BF');
        $this->addSql('DROP INDEX IDX_8D93D64910F532ED ON user');
        $this->addSql('ALTER TABLE user DROP extranet_user_linked_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}
