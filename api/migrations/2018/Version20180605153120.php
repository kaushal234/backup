<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180605153120 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE first_article_qualifications (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', location_id INT NOT NULL COMMENT \'(DC2Type:integer)\', buyer_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', poster_id INT NOT NULL COMMENT \'(DC2Type:integer)\', owner_id INT NOT NULL COMMENT \'(DC2Type:integer)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', supplier_number VARCHAR(6) DEFAULT NULL COMMENT \'(DC2Type:string)\', supplier_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', plan_definition_completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', plan_definition_due_date DATE NOT NULL, due_date DATE NOT NULL, express TINYINT(1) NOT NULL, eap INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', status VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', INDEX IDX_B5B031EFA58ECB40 (location_id), INDEX IDX_B5B031EF6C755722 (buyer_id), INDEX IDX_B5B031EF5BB66C05 (poster_id), INDEX IDX_B5B031EF7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE first_article_qualifications_members (first_article_qualification_id INT NOT NULL COMMENT \'(DC2Type:integer)\', people_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_5ED1B9A4C9C3E626 (first_article_qualification_id), INDEX IDX_5ED1B9A43147C936 (people_id), PRIMARY KEY(first_article_qualification_id, people_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE first_article_qualifications_plan_items (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', type_id INT NOT NULL COMMENT \'(DC2Type:integer)\', first_article_qualification_id INT NOT NULL COMMENT \'(DC2Type:integer)\', validated_by_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', requested_prior_delivery TINYINT(1) DEFAULT NULL, requested_at_purchase_order TINYINT(1) DEFAULT NULL, validated TINYINT(1) DEFAULT NULL, validated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_735A9E3DC54C8C93 (type_id), INDEX IDX_735A9E3DC9C3E626 (first_article_qualification_id), INDEX IDX_735A9E3DC69DE5E5 (validated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE first_article_qualifications_tags (id INT NOT NULL COMMENT \'(DC2Type:integer)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE first_article_qualifications_tags_xref (first_article_qualification_tag_id INT NOT NULL COMMENT \'(DC2Type:integer)\', first_article_qualification_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_764CF67F434430D8 (first_article_qualification_tag_id), INDEX IDX_764CF67FC9C3E626 (first_article_qualification_id), PRIMARY KEY(first_article_qualification_tag_id, first_article_qualification_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE first_article_qualifications_plan_item_types (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', description VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', requestable_prior_delivery TINYINT(1) NOT NULL, requestable_at_purchase_order TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE first_article_qualifications_files (id INT NOT NULL COMMENT \'(DC2Type:integer)\', first_article_qualification_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_5E0E5824C9C3E626 (first_article_qualification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE first_article_qualifications_part_numbers (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', first_article_qualification_id INT NOT NULL COMMENT \'(DC2Type:integer)\', number VARCHAR(16) NOT NULL COMMENT \'(DC2Type:string)\', revision VARCHAR(6) NOT NULL COMMENT \'(DC2Type:string)\', description VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', INDEX IDX_A8237AD7C9C3E626 (first_article_qualification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE first_article_qualifications ADD CONSTRAINT FK_B5B031EFA58ECB40 FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE first_article_qualifications ADD CONSTRAINT FK_B5B031EF6C755722 FOREIGN KEY (buyer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE first_article_qualifications ADD CONSTRAINT FK_B5B031EF5BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE first_article_qualifications ADD CONSTRAINT FK_B5B031EF7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_members ADD CONSTRAINT FK_5ED1B9A4C9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_members ADD CONSTRAINT FK_5ED1B9A43147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items ADD CONSTRAINT FK_735A9E3DC54C8C93 FOREIGN KEY (type_id) REFERENCES first_article_qualifications_plan_item_types (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items ADD CONSTRAINT FK_735A9E3DC9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items ADD CONSTRAINT FK_735A9E3DC69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_tags ADD CONSTRAINT FK_6AA2FC40BF396750 FOREIGN KEY (id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_tags_xref ADD CONSTRAINT FK_764CF67F434430D8 FOREIGN KEY (first_article_qualification_tag_id) REFERENCES first_article_qualifications_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_tags_xref ADD CONSTRAINT FK_764CF67FC9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_files ADD CONSTRAINT FK_5E0E5824C9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_files ADD CONSTRAINT FK_5E0E5824BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_part_numbers ADD CONSTRAINT FK_A8237AD7C9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id)');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_FAQ_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_FAQ_WRITE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_CMO"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FAQ_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_FAQ_CREATE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_CMO",
            "ROLE_ENG",
            "ROLE_BYR"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FAQ_BUYER")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_FAQ_BUYER"
          AND user_group.name in (
            "ROLE_BYR"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FAQ_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_FAQ_DELETE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_CMO"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FAQ_PLAN_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_FAQ_PLAN_WRITE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_CMO",
            "ROLE_COO",
            "ROLE_QAM"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FAQ_ITEM_TYPE_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_FAQ_ITEM_TYPE_WRITE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_CMO"
          )'
        );

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_FAQ_ITEM_TYPE_DELETE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_FAQ_ITEM_TYPE_DELETE"
          AND user_group.name in (
            "SUPERUSER",
            "ROLE_CMO"
          )'
        );

        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "FAI", 1, 0)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Data sheet", 1, 1)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Dimensional inspection reports", 1, 1)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Performance reports", 1, 1)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Manufacturing process (jigs…) validation", 1, 1)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Material and Performance Test Results", 1, 1)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Qualified Laboratory Documentation", 1, 1)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Qualification by ANALYSIS", 0, 0)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Qualification by SIMILARITY", 0, 0)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Qualification TEST: first installation", 1, 0)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Qualification TEST: Performance report ", 1, 0)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Qualification TEST: supplier participation", 1, 0)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Test bench (fatigue, heat, insulation, etc…)", 1, 0)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Unit test in factory", 1, 0)');
        $this->addSql('INSERT INTO first_article_qualifications_plan_item_types VALUES (NULL, "Unit test in the field", 1, 0)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE first_article_qualifications_members DROP FOREIGN KEY FK_5ED1B9A4C9C3E626');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items DROP FOREIGN KEY FK_735A9E3DC9C3E626');
        $this->addSql('ALTER TABLE first_article_qualifications_tags_xref DROP FOREIGN KEY FK_764CF67FC9C3E626');
        $this->addSql('ALTER TABLE first_article_qualifications_files DROP FOREIGN KEY FK_5E0E5824C9C3E626');
        $this->addSql('ALTER TABLE first_article_qualifications_part_numbers DROP FOREIGN KEY FK_A8237AD7C9C3E626');
        $this->addSql('ALTER TABLE first_article_qualifications_tags_xref DROP FOREIGN KEY FK_764CF67F434430D8');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items DROP FOREIGN KEY FK_735A9E3DC54C8C93');
        $this->addSql('DROP TABLE first_article_qualifications');
        $this->addSql('DROP TABLE first_article_qualifications_members');
        $this->addSql('DROP TABLE first_article_qualifications_plan_items');
        $this->addSql('DROP TABLE first_article_qualifications_tags');
        $this->addSql('DROP TABLE first_article_qualifications_tags_xref');
        $this->addSql('DROP TABLE first_article_qualifications_plan_item_types');
        $this->addSql('DROP TABLE first_article_qualifications_files');
        $this->addSql('DROP TABLE first_article_qualifications_part_numbers');
    }
}
