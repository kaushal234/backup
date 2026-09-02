<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160705100505 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE news (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, date DATE NOT NULL, legacy_id INT NOT NULL, INDEX IDX_1DD3995012469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, legacy_id INT NOT NULL, UNIQUE INDEX UNIQ_4F72BA905E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD3995012469DE2 FOREIGN KEY (category_id) REFERENCES news_category (id)');
        $this->addSql('ALTER TABLE user CHANGE locale locale VARCHAR(2) DEFAULT NULL');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_NEWS_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NEWS_WRITE"
                          AND user_group.name = "SUPERUSER"');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_NEWS_WRITE"
                          AND user_group.name = "ACL_NEWS"');
        $this->addSql('ALTER TABLE news ADD people_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD399503147C936 FOREIGN KEY (people_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1DD399503147C936 ON news (people_id)');
        $this->addSql('ALTER TABLE news ADD picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE news CHANGE date date DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
    }
}
