<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180720120655 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE first_article_qualifications_equipment_records (first_article_qualification_id INT NOT NULL COMMENT \'(DC2Type:integer)\', equipment_record_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_3ABF93F1C9C3E626 (first_article_qualification_id), INDEX IDX_3ABF93F19FC03375 (equipment_record_id), PRIMARY KEY(first_article_qualification_id, equipment_record_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE first_article_qualifications_equipment_records ADD CONSTRAINT FK_3ABF93F1C9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_equipment_records ADD CONSTRAINT FK_3ABF93F19FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications DROP FOREIGN KEY FK_B5B031EF5BB66C05');
        $this->addSql('ALTER TABLE first_article_qualifications DROP FOREIGN KEY FK_B5B031EF6C755722');
        $this->addSql('ALTER TABLE first_article_qualifications DROP FOREIGN KEY FK_B5B031EF7E3C61F9');
        $this->addSql('ALTER TABLE first_article_qualifications DROP FOREIGN KEY FK_B5B031EFA58ECB40');
        $this->addSql('ALTER TABLE first_article_qualifications ADD meap INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE buyer_id buyer_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE deleted_at deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', CHANGE supplier_number supplier_number VARCHAR(6) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE supplier_name supplier_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', CHANGE completed_at completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', CHANGE plan_definition_completed_at plan_definition_completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', CHANGE eap eap INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('DROP INDEX idx_b5b031efa58ecb40 ON first_article_qualifications');
        $this->addSql('CREATE INDEX IDX_2B0E80D364D218E ON first_article_qualifications (location_id)');
        $this->addSql('DROP INDEX idx_b5b031ef6c755722 ON first_article_qualifications');
        $this->addSql('CREATE INDEX IDX_2B0E80D36C755722 ON first_article_qualifications (buyer_id)');
        $this->addSql('DROP INDEX idx_b5b031ef5bb66c05 ON first_article_qualifications');
        $this->addSql('CREATE INDEX IDX_2B0E80D35BB66C05 ON first_article_qualifications (poster_id)');
        $this->addSql('DROP INDEX idx_b5b031ef7e3c61f9 ON first_article_qualifications');
        $this->addSql('CREATE INDEX IDX_2B0E80D37E3C61F9 ON first_article_qualifications (owner_id)');
        $this->addSql('ALTER TABLE first_article_qualifications ADD CONSTRAINT FK_B5B031EF5BB66C05 FOREIGN KEY (poster_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE first_article_qualifications ADD CONSTRAINT FK_B5B031EF6C755722 FOREIGN KEY (buyer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE first_article_qualifications ADD CONSTRAINT FK_B5B031EF7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE first_article_qualifications ADD CONSTRAINT FK_B5B031EFA58ECB40 FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_members DROP FOREIGN KEY FK_5ED1B9A43147C936');
        $this->addSql('ALTER TABLE first_article_qualifications_members DROP FOREIGN KEY FK_5ED1B9A4C9C3E626');
        $this->addSql('DROP INDEX idx_5ed1b9a4c9c3e626 ON first_article_qualifications_members');
        $this->addSql('CREATE INDEX IDX_BD77D7EAC9C3E626 ON first_article_qualifications_members (first_article_qualification_id)');
        $this->addSql('DROP INDEX idx_5ed1b9a43147c936 ON first_article_qualifications_members');
        $this->addSql('CREATE INDEX IDX_BD77D7EA3147C936 ON first_article_qualifications_members (people_id)');
        $this->addSql('ALTER TABLE first_article_qualifications_members ADD CONSTRAINT FK_5ED1B9A43147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_members ADD CONSTRAINT FK_5ED1B9A4C9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items DROP FOREIGN KEY FK_735A9E3DC54C8C93');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items DROP FOREIGN KEY FK_735A9E3DC69DE5E5');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items DROP FOREIGN KEY FK_735A9E3DC9C3E626');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items CHANGE validated_by_id validated_by_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', CHANGE requested_prior_delivery requested_prior_delivery TINYINT(1) DEFAULT NULL, CHANGE requested_at_purchase_order requested_at_purchase_order TINYINT(1) DEFAULT NULL, CHANGE validated validated TINYINT(1) DEFAULT NULL, CHANGE validated_at validated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('DROP INDEX idx_735a9e3dc54c8c93 ON first_article_qualifications_plan_items');
        $this->addSql('CREATE INDEX IDX_CD957EE0C54C8C93 ON first_article_qualifications_plan_items (type_id)');
        $this->addSql('DROP INDEX idx_735a9e3dc9c3e626 ON first_article_qualifications_plan_items');
        $this->addSql('CREATE INDEX IDX_CD957EE0C9C3E626 ON first_article_qualifications_plan_items (first_article_qualification_id)');
        $this->addSql('DROP INDEX idx_735a9e3dc69de5e5 ON first_article_qualifications_plan_items');
        $this->addSql('CREATE INDEX IDX_CD957EE0C69DE5E5 ON first_article_qualifications_plan_items (validated_by_id)');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items ADD CONSTRAINT FK_735A9E3DC54C8C93 FOREIGN KEY (type_id) REFERENCES first_article_qualifications_plan_item_types (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items ADD CONSTRAINT FK_735A9E3DC69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items ADD CONSTRAINT FK_735A9E3DC9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_tags_xref DROP FOREIGN KEY FK_764CF67F434430D8');
        $this->addSql('ALTER TABLE first_article_qualifications_tags_xref DROP FOREIGN KEY FK_764CF67FC9C3E626');
        $this->addSql('DROP INDEX idx_764cf67f434430d8 ON first_article_qualifications_tags_xref');
        $this->addSql('CREATE INDEX IDX_2C99F6C2434430D8 ON first_article_qualifications_tags_xref (first_article_qualification_tag_id)');
        $this->addSql('DROP INDEX idx_764cf67fc9c3e626 ON first_article_qualifications_tags_xref');
        $this->addSql('CREATE INDEX IDX_2C99F6C2C9C3E626 ON first_article_qualifications_tags_xref (first_article_qualification_id)');
        $this->addSql('ALTER TABLE first_article_qualifications_tags_xref ADD CONSTRAINT FK_764CF67F434430D8 FOREIGN KEY (first_article_qualification_tag_id) REFERENCES first_article_qualifications_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_tags_xref ADD CONSTRAINT FK_764CF67FC9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE first_article_qualifications_files DROP FOREIGN KEY FK_5E0E5824C9C3E626');
        $this->addSql('ALTER TABLE first_article_qualifications_files CHANGE first_article_qualification_id first_article_qualification_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('DROP INDEX idx_5e0e5824c9c3e626 ON first_article_qualifications_files');
        $this->addSql('CREATE INDEX IDX_7D86ACE4C9C3E626 ON first_article_qualifications_files (first_article_qualification_id)');
        $this->addSql('ALTER TABLE first_article_qualifications_files ADD CONSTRAINT FK_5E0E5824C9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id)');
        $this->addSql('ALTER TABLE first_article_qualifications_part_numbers DROP FOREIGN KEY FK_A8237AD7C9C3E626');
        $this->addSql('DROP INDEX idx_a8237ad7c9c3e626 ON first_article_qualifications_part_numbers');
        $this->addSql('CREATE INDEX IDX_3CBF9D32C9C3E626 ON first_article_qualifications_part_numbers (first_article_qualification_id)');
        $this->addSql('ALTER TABLE first_article_qualifications_part_numbers ADD CONSTRAINT FK_A8237AD7C9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id)');
    }

    public function down(Schema $schema): void
    {
    }
}
