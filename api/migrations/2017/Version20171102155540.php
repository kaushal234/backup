<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171102155540 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE survey_published_surveys DROP FOREIGN KEY FK_2F25A88C158E0B66');
        $this->addSql('DROP INDEX IDX_2F25A88C158E0B66 ON survey_published_surveys');
        $this->addSql('ALTER TABLE survey_published_surveys ADD people_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', ADD customer_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', DROP target_id, CHANGE survey_id survey_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE deletedAt deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE survey_published_surveys ADD CONSTRAINT FK_2F25A88C3147C936 FOREIGN KEY (people_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_published_surveys ADD CONSTRAINT FK_2F25A88C9395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2F25A88C3147C936 ON survey_published_surveys (people_id)');
        $this->addSql('CREATE INDEX IDX_2F25A88C9395C3F3 ON survey_published_surveys (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
