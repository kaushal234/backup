<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220330082249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add comment files and discriminator';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment_files (id INT NOT NULL, comment_id BIGINT DEFAULT NULL, INDEX IDX_AFAFD8A8F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment_files ADD CONSTRAINT FK_AFAFD8A8F8697D13 FOREIGN KEY (comment_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE comment_files ADD CONSTRAINT FK_AFAFD8A8BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity ADD discriminator VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE comment_files');
        $this->addSql('ALTER TABLE activity DROP discriminator');
    }
}
