<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170715134733 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE survey_rating_types (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', survey_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', min INT NOT NULL COMMENT \'(DC2Type:integer)\', max INT NOT NULL COMMENT \'(DC2Type:integer)\', min_label VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', max_label VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', description LONGTEXT NOT NULL, deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_B4F48A5CB3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_published_surveys (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', survey_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', target_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', token VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\',deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', target_type VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX UNIQ_2F25A88C5F37A13B (token), INDEX IDX_2F25A88CB3FE509D (survey_id), INDEX IDX_2F25A88C158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_groups (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', survey_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', created_by INT NOT NULL COMMENT \'(DC2Type:integer)\', updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', description LONGTEXT DEFAULT NULL, sorting INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_D863F11CB3FE509D (survey_id), INDEX IDX_D863F11CDE12AB56 (created_by), INDEX IDX_D863F11C16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE surveys (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', created_by INT NOT NULL COMMENT \'(DC2Type:integer)\', updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', description LONGTEXT DEFAULT NULL, expiration_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_AFA82EA7DE12AB56 (created_by), INDEX IDX_AFA82EA716FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_answers (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', published_survey_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', item_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', rating_type_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', created_by INT NOT NULL COMMENT \'(DC2Type:integer)\', updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', value INT NOT NULL COMMENT \'(DC2Type:integer)\', deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_14FCE5BDD48AE05 (published_survey_id), INDEX IDX_14FCE5BD126F525E (item_id), INDEX IDX_14FCE5BD260075EB (rating_type_id), INDEX IDX_14FCE5BDDE12AB56 (created_by), INDEX IDX_14FCE5BD16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_items (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', group_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', survey_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', created_by INT NOT NULL COMMENT \'(DC2Type:integer)\', updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', description LONGTEXT NOT NULL, deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_ED6E1B75FE54D947 (group_id), INDEX IDX_ED6E1B75B3FE509D (survey_id), INDEX IDX_ED6E1B75DE12AB56 (created_by), INDEX IDX_ED6E1B7516FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_comments (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', item_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', published_survey_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', created_by INT NOT NULL COMMENT \'(DC2Type:integer)\', updated_by INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', content LONGTEXT NOT NULL, deletedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_369D00D126F525E (item_id), INDEX IDX_369D00DD48AE05 (published_survey_id), INDEX IDX_369D00DDE12AB56 (created_by), INDEX IDX_369D00D16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE survey_rating_types ADD CONSTRAINT FK_B4F48A5CB3FE509D FOREIGN KEY (survey_id) REFERENCES surveys (id)');
        $this->addSql('ALTER TABLE survey_published_surveys ADD CONSTRAINT FK_2F25A88CB3FE509D FOREIGN KEY (survey_id) REFERENCES surveys (id)');
        $this->addSql('ALTER TABLE survey_published_surveys ADD CONSTRAINT FK_2F25A88C158E0B66 FOREIGN KEY (target_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_groups ADD CONSTRAINT FK_D863F11CB3FE509D FOREIGN KEY (survey_id) REFERENCES surveys (id)');
        $this->addSql('ALTER TABLE survey_groups ADD CONSTRAINT FK_D863F11CDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_groups ADD CONSTRAINT FK_D863F11C16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE surveys ADD CONSTRAINT FK_AFA82EA7DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE surveys ADD CONSTRAINT FK_AFA82EA716FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_answers ADD CONSTRAINT FK_14FCE5BDD48AE05 FOREIGN KEY (published_survey_id) REFERENCES survey_published_surveys (id)');
        $this->addSql('ALTER TABLE survey_answers ADD CONSTRAINT FK_14FCE5BD126F525E FOREIGN KEY (item_id) REFERENCES survey_items (id)');
        $this->addSql('ALTER TABLE survey_answers ADD CONSTRAINT FK_14FCE5BD260075EB FOREIGN KEY (rating_type_id) REFERENCES survey_rating_types (id)');
        $this->addSql('ALTER TABLE survey_answers ADD CONSTRAINT FK_14FCE5BDDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_answers ADD CONSTRAINT FK_14FCE5BD16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_items ADD CONSTRAINT FK_ED6E1B75FE54D947 FOREIGN KEY (group_id) REFERENCES survey_groups (id)');
        $this->addSql('ALTER TABLE survey_items ADD CONSTRAINT FK_ED6E1B75B3FE509D FOREIGN KEY (survey_id) REFERENCES surveys (id)');
        $this->addSql('ALTER TABLE survey_items ADD CONSTRAINT FK_ED6E1B75DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_items ADD CONSTRAINT FK_ED6E1B7516FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_comments ADD CONSTRAINT FK_369D00D126F525E FOREIGN KEY (item_id) REFERENCES survey_items (id)');
        $this->addSql('ALTER TABLE survey_comments ADD CONSTRAINT FK_369D00DD48AE05 FOREIGN KEY (published_survey_id) REFERENCES survey_published_surveys (id)');
        $this->addSql('ALTER TABLE survey_comments ADD CONSTRAINT FK_369D00DDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_comments ADD CONSTRAINT FK_369D00D16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        //        $this->addSql('ALTER TABLE spq_quotation_lines CHANGE quotation_id quotation_id INT NOT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', locale VARCHAR(8) NOT NULL COMMENT \'(DC2Type:string)\', object_class VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', field VARCHAR(32) NOT NULL COMMENT \'(DC2Type:string)\', foreign_key VARCHAR(64) NOT NULL COMMENT \'(DC2Type:string)\', content LONGTEXT DEFAULT NULL, INDEX translations_lookup_idx (locale, object_class, foreign_key), UNIQUE INDEX lookup_unique_idx (locale, object_class, field, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE survey_answers DROP FOREIGN KEY FK_14FCE5BD260075EB');
        $this->addSql('ALTER TABLE survey_answers DROP FOREIGN KEY FK_14FCE5BDD48AE05');
        $this->addSql('ALTER TABLE survey_comments DROP FOREIGN KEY FK_369D00DD48AE05');
        $this->addSql('ALTER TABLE survey_items DROP FOREIGN KEY FK_ED6E1B75FE54D947');
        $this->addSql('ALTER TABLE survey_rating_types DROP FOREIGN KEY FK_B4F48A5CB3FE509D');
        $this->addSql('ALTER TABLE survey_published_surveys DROP FOREIGN KEY FK_2F25A88CB3FE509D');
        $this->addSql('ALTER TABLE survey_groups DROP FOREIGN KEY FK_D863F11CB3FE509D');
        $this->addSql('ALTER TABLE survey_items DROP FOREIGN KEY FK_ED6E1B75B3FE509D');
        $this->addSql('ALTER TABLE survey_answers DROP FOREIGN KEY FK_14FCE5BD126F525E');
        $this->addSql('ALTER TABLE survey_comments DROP FOREIGN KEY FK_369D00D126F525E');
        $this->addSql('DROP TABLE survey_rating_types');
        $this->addSql('DROP TABLE survey_published_surveys');
        $this->addSql('DROP TABLE survey_groups');
        $this->addSql('DROP TABLE surveys');
        $this->addSql('DROP TABLE survey_answers');
        $this->addSql('DROP TABLE survey_items');
        $this->addSql('DROP TABLE survey_comments');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('ALTER TABLE spq_quotation_lines CHANGE quotation_id quotation_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
    }
}
