<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191115160638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE meetings (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, confidential TINYINT(1) NOT NULL, status VARCHAR(8) NOT NULL, title VARCHAR(128) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, phone_call TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, suppliers TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', INDEX IDX_44FE52E2DE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_competitor (meeting_id INT NOT NULL, competitor_id INT NOT NULL, INDEX IDX_5945C51467433D9C (meeting_id), INDEX IDX_5945C51478A5D405 (competitor_id), PRIMARY KEY(meeting_id, competitor_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_customer (meeting_id INT NOT NULL, customer_id INT NOT NULL, INDEX IDX_12A8A3DC67433D9C (meeting_id), INDEX IDX_12A8A3DC9395C3F3 (customer_id), PRIMARY KEY(meeting_id, customer_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_extranet_user (meeting_id INT NOT NULL, extranet_user_id INT NOT NULL, INDEX IDX_E6897A7167433D9C (meeting_id), INDEX IDX_E6897A71D2CDD54B (extranet_user_id), PRIMARY KEY(meeting_id, extranet_user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_product_type (meeting_id INT NOT NULL, product_type_id INT NOT NULL, INDEX IDX_2175903567433D9C (meeting_id), INDEX IDX_2175903514959723 (product_type_id), PRIMARY KEY(meeting_id, product_type_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_location (meeting_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_CD0FA41E67433D9C (meeting_id), INDEX IDX_CD0FA41E64D218E (location_id), PRIMARY KEY(meeting_id, location_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_people (meeting_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_287E5B6467433D9C (meeting_id), INDEX IDX_287E5B643147C936 (people_id), PRIMARY KEY(meeting_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_files (id INT NOT NULL, meeting_id INT DEFAULT NULL, INDEX IDX_6E04025967433D9C (meeting_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_contacts (id INT AUTO_INCREMENT NOT NULL, meeting_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, mail VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A0D138A667433D9C (meeting_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_actions (id INT AUTO_INCREMENT NOT NULL, assignee_id INT DEFAULT NULL, created_by INT DEFAULT NULL, meeting_id INT NOT NULL, completed TINYINT(1) NOT NULL, description VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, task INT NOT NULL, closing_comment VARCHAR(255) DEFAULT NULL, INDEX IDX_9D9AB96259EC7D60 (assignee_id), INDEX IDX_9D9AB962DE12AB56 (created_by), INDEX IDX_9D9AB96267433D9C (meeting_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meetings ADD CONSTRAINT FK_44FE52E2DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meeting_competitor ADD CONSTRAINT FK_5945C51467433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_competitor ADD CONSTRAINT FK_5945C51478A5D405 FOREIGN KEY (competitor_id) REFERENCES competitors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_customer ADD CONSTRAINT FK_12A8A3DC67433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_customer ADD CONSTRAINT FK_12A8A3DC9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_extranet_user ADD CONSTRAINT FK_E6897A7167433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_extranet_user ADD CONSTRAINT FK_E6897A71D2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_product_type ADD CONSTRAINT FK_2175903567433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_product_type ADD CONSTRAINT FK_2175903514959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_location ADD CONSTRAINT FK_CD0FA41E67433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_location ADD CONSTRAINT FK_CD0FA41E64D218E FOREIGN KEY (location_id) REFERENCES directory_location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_people ADD CONSTRAINT FK_287E5B6467433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_people ADD CONSTRAINT FK_287E5B643147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_files ADD CONSTRAINT FK_6E04025967433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id)');
        $this->addSql('ALTER TABLE meeting_files ADD CONSTRAINT FK_6E040259BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_contacts ADD CONSTRAINT FK_A0D138A667433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id)');
        $this->addSql('ALTER TABLE meeting_actions ADD CONSTRAINT FK_9D9AB96259EC7D60 FOREIGN KEY (assignee_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meeting_actions ADD CONSTRAINT FK_9D9AB962DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meeting_actions ADD CONSTRAINT FK_9D9AB96267433D9C FOREIGN KEY (meeting_id) REFERENCES meetings (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MEETING_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_MEETING_WRITE"
                          AND user_group.name = "SUPERUSER"');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_MEETING_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_MEETING_READ"
                          AND user_group.name = "SUPERUSER"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_competitor DROP FOREIGN KEY FK_5945C51467433D9C');
        $this->addSql('ALTER TABLE meeting_customer DROP FOREIGN KEY FK_12A8A3DC67433D9C');
        $this->addSql('ALTER TABLE meeting_extranet_user DROP FOREIGN KEY FK_E6897A7167433D9C');
        $this->addSql('ALTER TABLE meeting_product_type DROP FOREIGN KEY FK_2175903567433D9C');
        $this->addSql('ALTER TABLE meeting_location DROP FOREIGN KEY FK_CD0FA41E67433D9C');
        $this->addSql('ALTER TABLE meeting_people DROP FOREIGN KEY FK_287E5B6467433D9C');
        $this->addSql('ALTER TABLE meeting_files DROP FOREIGN KEY FK_6E04025967433D9C');
        $this->addSql('ALTER TABLE meeting_contacts DROP FOREIGN KEY FK_A0D138A667433D9C');
        $this->addSql('ALTER TABLE meeting_actions DROP FOREIGN KEY FK_9D9AB96267433D9C');
        $this->addSql('DROP TABLE meetings');
        $this->addSql('DROP TABLE meeting_competitor');
        $this->addSql('DROP TABLE meeting_customer');
        $this->addSql('DROP TABLE meeting_extranet_user');
        $this->addSql('DROP TABLE meeting_product_type');
        $this->addSql('DROP TABLE meeting_location');
        $this->addSql('DROP TABLE meeting_people');
        $this->addSql('DROP TABLE trainings');
        $this->addSql('DROP TABLE meeting_files');
        $this->addSql('DROP TABLE meeting_contacts');
        $this->addSql('DROP TABLE meeting_actions');
    }
}
