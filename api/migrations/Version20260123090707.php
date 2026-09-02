<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123090707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create AI Logs table + request and response';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ai_files (request_id INT DEFAULT NULL, id INT NOT NULL, UNIQUE INDEX UNIQ_5A827B1C427EB8A5 (request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE ai_logs (created_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, people_id INT DEFAULT NULL, INDEX IDX_F4DE87E73147C936 (people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE ai_requests (url VARCHAR(255) NOT NULL, options JSON NOT NULL, created_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, log_id INT DEFAULT NULL, response_id INT DEFAULT NULL, INDEX IDX_64635031EA675D86 (log_id), UNIQUE INDEX UNIQ_64635031FBF32840 (response_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE ai_responses (content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE ai_files ADD CONSTRAINT FK_5A827B1C427EB8A5 FOREIGN KEY (request_id) REFERENCES ai_requests (id)');
        $this->addSql('ALTER TABLE ai_files ADD CONSTRAINT FK_5A827B1CBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_logs ADD CONSTRAINT FK_F4DE87E73147C936 FOREIGN KEY (people_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ai_requests ADD CONSTRAINT FK_64635031EA675D86 FOREIGN KEY (log_id) REFERENCES ai_logs (id)');
        $this->addSql('ALTER TABLE ai_requests ADD CONSTRAINT FK_64635031FBF32840 FOREIGN KEY (response_id) REFERENCES ai_responses (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_files DROP FOREIGN KEY FK_5A827B1C427EB8A5');
        $this->addSql('ALTER TABLE ai_files DROP FOREIGN KEY FK_5A827B1CBF396750');
        $this->addSql('ALTER TABLE ai_logs DROP FOREIGN KEY FK_F4DE87E73147C936');
        $this->addSql('ALTER TABLE ai_requests DROP FOREIGN KEY FK_64635031EA675D86');
        $this->addSql('ALTER TABLE ai_requests DROP FOREIGN KEY FK_64635031FBF32840');
        $this->addSql('DROP TABLE ai_files');
        $this->addSql('DROP TABLE ai_logs');
        $this->addSql('DROP TABLE ai_requests');
        $this->addSql('DROP TABLE ai_responses');
    }
}
