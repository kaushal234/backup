<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171214105421 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE survey_campaigns ADD deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', CHANGE model_id model_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE created_by created_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE sent_at sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema): void
    {
    }
}
