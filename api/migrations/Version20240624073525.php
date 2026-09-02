<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240624073525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update MIM module with new features';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market_intelligence_position_level (market_intelligence_id INT NOT NULL, position_level_id INT NOT NULL, INDEX IDX_F363FEFD0DF19F7 (market_intelligence_id), INDEX IDX_F363FEF7B961910 (position_level_id), PRIMARY KEY(market_intelligence_id, position_level_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE market_intelligence_division (market_intelligence_id INT NOT NULL, division_id INT NOT NULL, INDEX IDX_E273AA15D0DF19F7 (market_intelligence_id), INDEX IDX_E273AA1541859289 (division_id), PRIMARY KEY(market_intelligence_id, division_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE market_intelligence_position_level ADD CONSTRAINT FK_F363FEFD0DF19F7 FOREIGN KEY (market_intelligence_id) REFERENCES market_intelligence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence_position_level ADD CONSTRAINT FK_F363FEF7B961910 FOREIGN KEY (position_level_id) REFERENCES directory_position_level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence_division ADD CONSTRAINT FK_E273AA15D0DF19F7 FOREIGN KEY (market_intelligence_id) REFERENCES market_intelligence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence_division ADD CONSTRAINT FK_E273AA1541859289 FOREIGN KEY (division_id) REFERENCES directory_division (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE market_intelligence ADD suppliers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE market_intelligence_subscription ADD supplier VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE market_intelligence_position_level DROP FOREIGN KEY FK_F363FEFD0DF19F7');
        $this->addSql('ALTER TABLE market_intelligence_position_level DROP FOREIGN KEY FK_F363FEF7B961910');
        $this->addSql('ALTER TABLE market_intelligence_division DROP FOREIGN KEY FK_E273AA15D0DF19F7');
        $this->addSql('ALTER TABLE market_intelligence_division DROP FOREIGN KEY FK_E273AA1541859289');
        $this->addSql('DROP TABLE market_intelligence_position_level');
        $this->addSql('DROP TABLE market_intelligence_division');
        $this->addSql('ALTER TABLE market_intelligence DROP suppliers');
        $this->addSql('ALTER TABLE market_intelligence_subscription DROP supplier');
    }
}
