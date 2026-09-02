<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180830072205 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE demos ADD last_commented_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', ADD actual_start_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', CHANGE start_date expected_start_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\',CHANGE end_date expected_end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE demos DROP actual_start_date, DROP last_commented_at, CHANGE expected_start_date start_date  DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\',CHANGE expected_end_date end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }
}
