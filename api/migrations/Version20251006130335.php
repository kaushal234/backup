<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251006130335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Document Translation Table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE document_translation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, document_id VARCHAR(128) NOT NULL, document_key VARCHAR(256) NOT NULL, estimated_seconds INT DEFAULT NULL, target_lang VARCHAR(12) NOT NULL, filename VARCHAR(255) NOT NULL, mime_type VARCHAR(128) NOT NULL, size INT DEFAULT NULL, status VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, INDEX IDX_36C07205A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document_translation ADD CONSTRAINT FK_36C07205A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document_translation DROP FOREIGN KEY FK_36C07205A76ED395');
        $this->addSql('DROP TABLE document_translation');
    }
}
