<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170608070302 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE location_cleanliness (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, user_id INT NOT NULL, rating DOUBLE PRECISION NOT NULL, date DATE NOT NULL, INDEX IDX_1D6C484C64D218E (location_id), INDEX IDX_1D6C484CA76ED395 (user_id), UNIQUE INDEX unique_rating_per_month_per_location (location_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE location_cleanliness ADD CONSTRAINT FK_1D6C484C64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE location_cleanliness ADD CONSTRAINT FK_1D6C484CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE calibration_log DROP INDEX IDX_F68D4792046B8D5, ADD UNIQUE INDEX UNIQ_F68D4792046B8D5 (out_of_tolerance_form_id)');
        $this->addSql('ALTER TABLE out_of_tolerance_form DROP files');
        $this->addSql('ALTER TABLE tool CHANGE tool_type_id tool_type_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE location_cleanliness');
        $this->addSql('ALTER TABLE calibration_log DROP INDEX UNIQ_F68D4792046B8D5, ADD INDEX IDX_F68D4792046B8D5 (out_of_tolerance_form_id)');
        $this->addSql('ALTER TABLE out_of_tolerance_form ADD files LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE tool CHANGE tool_type_id tool_type_id INT NOT NULL');
    }
}
