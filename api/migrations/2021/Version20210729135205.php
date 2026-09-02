<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210729135205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD mentor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_28166A26DB403044 FOREIGN KEY (mentor_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649DB403044 ON user (mentor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_28166A26DB403044');
        $this->addSql('DROP INDEX IDX_8D93D649DB403044 ON user');
        $this->addSql('ALTER TABLE user DROP mentor_id');
    }
}
