<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240105100320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove useless property on MIM and MIM subscriptions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_5DA9408D8CDE5729 ON market_intelligence');
        $this->addSql('ALTER TABLE market_intelligence DROP type');
        $this->addSql('ALTER TABLE market_intelligence_subscription DROP mim_type');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_intelligence_subscription ADD mim_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE market_intelligence ADD type VARCHAR(50) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_5DA9408D8CDE5729 ON market_intelligence (type)');
    }
}
