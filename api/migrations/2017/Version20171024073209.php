<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171024073209 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE survey_answers DROP FOREIGN KEY FK_14FCE5BD16FE72E1');
        $this->addSql('ALTER TABLE survey_answers DROP FOREIGN KEY FK_14FCE5BDDE12AB56');
        $this->addSql('DROP INDEX IDX_14FCE5BDDE12AB56 ON survey_answers');
        $this->addSql('DROP INDEX IDX_14FCE5BD16FE72E1 ON survey_answers');
        $this->addSql('ALTER TABLE survey_answers DROP created_by, DROP updated_by, CHANGE published_survey_id published_survey_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE item_id item_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE rating_type_id rating_type_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE deletedAt deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');

        $this->addSql('ALTER TABLE survey_comments DROP FOREIGN KEY FK_369D00D16FE72E1');
        $this->addSql('ALTER TABLE survey_comments DROP FOREIGN KEY FK_369D00DDE12AB56');
        $this->addSql('DROP INDEX IDX_369D00DDE12AB56 ON survey_comments');
        $this->addSql('DROP INDEX IDX_369D00D16FE72E1 ON survey_comments');
        $this->addSql('ALTER TABLE survey_comments DROP created_by, DROP updated_by, CHANGE item_id item_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE published_survey_id published_survey_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE deletedAt deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema): void
    {
    }
}
