<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180703133610 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE forecast_closures ADD competitor_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', ADD reason LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE forecast_closures ADD CONSTRAINT FK_B6C68D9278A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE forecast_closures DROP FOREIGN KEY FK_B6C68D9278A5D405');
        $this->addSql('DROP INDEX IDX_B6C68D9278A5D405 ON forecast_closures');
        $this->addSql('ALTER TABLE forecast_closures DROP competitor_id, DROP reason');
    }
}
