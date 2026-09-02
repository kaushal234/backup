<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211130135534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'change parent column name of ManualDocumentFile from document_id to manual_document_id';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manual_document_files DROP FOREIGN KEY FK_1CE23BEEC33F7837');
        $this->addSql('DROP INDEX IDX_1CE23BEEC33F7837 ON manual_document_files');
        $this->addSql('ALTER TABLE manual_document_files CHANGE document_id manual_document_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE manual_document_files ADD CONSTRAINT FK_1CE23BEEC8EC4AEC FOREIGN KEY (manual_document_id) REFERENCES manual_documents (id)');
        $this->addSql('CREATE INDEX IDX_1CE23BEEC8EC4AEC ON manual_document_files (manual_document_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manual_document_files DROP FOREIGN KEY FK_1CE23BEEC8EC4AEC');
        $this->addSql('DROP INDEX IDX_1CE23BEEC8EC4AEC ON manual_document_files');
        $this->addSql('ALTER TABLE manual_document_files CHANGE manual_document_id document_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE manual_document_files ADD CONSTRAINT FK_1CE23BEEC33F7837 FOREIGN KEY (document_id) REFERENCES manual_documents (id)');
        $this->addSql('CREATE INDEX IDX_1CE23BEEC33F7837 ON manual_document_files (document_id)');
    }
}
