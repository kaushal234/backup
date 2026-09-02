<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316214859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update AI Log for chatbot';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_logs ADD conversation_summary LONGTEXT DEFAULT NULL, ADD summarized_requests_count INT DEFAULT 0 NOT NULL, ADD type VARCHAR(255) DEFAULT NULL, ADD title VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ai_ratings CHANGE comment comment VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE ai_ratings RENAME INDEX uniq_f2b3091aea675d86 TO unique_rating_log');
        $this->addSql('ALTER TABLE ai_requests DROP FOREIGN KEY FK_64635031FBF32840');
        $this->addSql('ALTER TABLE ai_requests ADD content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_requests ADD CONSTRAINT FK_64635031FBF32840 FOREIGN KEY (response_id) REFERENCES ai_responses (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_responses CHANGE content content LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE dms ADD revision VARCHAR(255) DEFAULT NULL, ADD filepath VARCHAR(255) DEFAULT NULL, ADD mimetype VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_logs DROP conversation_summary, DROP summarized_requests_count, DROP type, DROP title');
        $this->addSql('ALTER TABLE dms DROP revision, DROP filepath, DROP mimetype');
        $this->addSql('ALTER TABLE ai_ratings CHANGE comment comment VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_ratings RENAME INDEX unique_rating_log TO UNIQ_F2B3091AEA675D86');
        $this->addSql('ALTER TABLE ai_requests DROP FOREIGN KEY FK_64635031FBF32840');
        $this->addSql('ALTER TABLE ai_requests DROP content');
        $this->addSql('ALTER TABLE ai_requests ADD CONSTRAINT FK_64635031FBF32840 FOREIGN KEY (response_id) REFERENCES ai_responses (id)');
        $this->addSql('ALTER TABLE ai_responses CHANGE content content LONGTEXT NOT NULL');
    }
}
