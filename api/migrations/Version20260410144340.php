<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260410144340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update foreign keys that were misconfigured';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_requests DROP FOREIGN KEY FK_64635031EA675D86');
        $this->addSql('ALTER TABLE ai_requests ADD CONSTRAINT FK_64635031EA675D86 FOREIGN KEY (log_id) REFERENCES ai_logs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_files DROP FOREIGN KEY FK_5A827B1C427EB8A5');
        $this->addSql('ALTER TABLE ai_files ADD CONSTRAINT FK_5A827B1C427EB8A5 FOREIGN KEY (request_id) REFERENCES ai_requests (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_requests DROP FOREIGN KEY FK_64635031EA675D86');
        $this->addSql('ALTER TABLE ai_requests ADD CONSTRAINT FK_64635031EA675D86 FOREIGN KEY (log_id) REFERENCES ai_logs (id)');
        $this->addSql('ALTER TABLE ai_files DROP FOREIGN KEY FK_5A827B1C427EB8A5');
        $this->addSql('ALTER TABLE ai_files ADD CONSTRAINT FK_5A827B1C427EB8A5 FOREIGN KEY (request_id) REFERENCES ai_requests (id)');
    }
}
