<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221122124216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add resource for evendors news with factories collection and files collection.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evendors_news (id INT AUTO_INCREMENT NOT NULL, created_by INT NOT NULL, content LONGTEXT NOT NULL, published_at DATETIME NOT NULL, unpublished_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_41A3C7E6B03A8386 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evendors_news_location (evendors_news_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_FB566ADE4ADAB8 (evendors_news_id), INDEX IDX_FB566ADE64D218E (location_id), PRIMARY KEY(evendors_news_id, location_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evendors_news_files (id INT NOT NULL, evendors_news_id INT DEFAULT NULL, INDEX IDX_39A3318D4ADAB8 (evendors_news_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evendors_news ADD CONSTRAINT FK_41A3C7E6B03A8386 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evendors_news_location ADD CONSTRAINT FK_FB566ADE4ADAB8 FOREIGN KEY (evendors_news_id) REFERENCES evendors_news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evendors_news_location ADD CONSTRAINT FK_FB566ADE64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evendors_news_files ADD CONSTRAINT FK_39A3318D4ADAB8 FOREIGN KEY (evendors_news_id) REFERENCES evendors_news (id)');
        $this->addSql('ALTER TABLE evendors_news_files ADD CONSTRAINT FK_39A3318DBF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("AUTHORIZED_APPLICATION_FEATURE_EVENDORS_NEWS_READ")');
        $this->addSql('INSERT IGNORE INTO feature_authorized_application(feature_id, authorized_application_id)
                           SELECT (
                               SELECT feature.id FROM feature WHERE feature.name = "AUTHORIZED_APPLICATION_FEATURE_EVENDORS_NEWS_READ"),
                               (SELECT id FROM authorized_application  WHERE name="evendors"
                           )');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_EVENDORS_NEWS_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                        SELECT user_group.id, feature.id
                        FROM user_group, feature
                        WHERE feature.name = "FEATURE_EVENDORS_NEWS_WRITE"
                        AND user_group.name IN ("SUPERUSER", "ROLE_MLM", "ROLE_BYR")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evendors_news_location DROP FOREIGN KEY FK_FB566ADE4ADAB8');
        $this->addSql('ALTER TABLE evendors_news_files DROP FOREIGN KEY FK_39A3318D4ADAB8');
        $this->addSql('DROP TABLE evendors_news');
        $this->addSql('DROP TABLE evendors_news_location');
        $this->addSql('DROP TABLE evendors_news_files');
    }
}
