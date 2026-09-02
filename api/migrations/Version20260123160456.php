<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123160456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ratings for AI logs.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ai_ratings (rating INT NOT NULL, comment VARCHAR(255) DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, log_id INT NOT NULL, UNIQUE INDEX UNIQ_F2B3091AEA675D86 (log_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE ai_ratings ADD CONSTRAINT FK_F2B3091AEA675D86 FOREIGN KEY (log_id) REFERENCES ai_logs (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_ratings DROP FOREIGN KEY FK_F2B3091AEA675D86');
        $this->addSql('DROP TABLE ai_ratings');
    }
}
