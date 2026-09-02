<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240808135546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allows null value for Supplier column on MIM subscription';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_intelligence_subscription CHANGE supplier supplier VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE market_intelligence_subscription SET supplier = NULL WHERE supplier = ''");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_intelligence_subscription CHANGE supplier supplier VARCHAR(255) NOT NULL');
        $this->addSql("UPDATE market_intelligence_subscription SET supplier = '' WHERE supplier IS NULL");
    }
}
