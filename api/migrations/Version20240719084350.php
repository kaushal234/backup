<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240719084350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'set survey questions and answers for CSR Commissioning';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer_survey_customer_service_record (commissioning_customer_service_record_id INT NOT NULL, question_survey_customer_service_record_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, answer LONGTEXT DEFAULT NULL, legacy_id INT NOT NULL, INDEX IDX_506853C5ED7607BD (commissioning_customer_service_record_id), INDEX IDX_506853C592CBCBBE (question_survey_customer_service_record_id), INDEX IDX_506853C5B03A8386 (created_by_id), INDEX IDX_506853C5896DBBDE (updated_by_id), PRIMARY KEY(commissioning_customer_service_record_id, question_survey_customer_service_record_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question_survey_customer_service_record (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(55) NOT NULL, type TINYTEXT NOT NULL, UNIQUE INDEX UNIQ_96C1BE585E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answer_survey_customer_service_record ADD CONSTRAINT FK_506853C5ED7607BD FOREIGN KEY (commissioning_customer_service_record_id) REFERENCES customer_service_record (id)');
        $this->addSql('ALTER TABLE answer_survey_customer_service_record ADD CONSTRAINT FK_506853C592CBCBBE FOREIGN KEY (question_survey_customer_service_record_id) REFERENCES question_survey_customer_service_record (id)');
        $this->addSql('ALTER TABLE answer_survey_customer_service_record ADD CONSTRAINT FK_506853C5B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE answer_survey_customer_service_record ADD CONSTRAINT FK_506853C5896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_service_record DROP survey_aspect, DROP survey_aspect_comment, DROP survey_conformity, DROP survey_conformity_comment, DROP survey_operational, DROP survey_operational_comment, DROP survey_shipping, DROP survey_shipping_comment');

        $this->addSql("INSERT INTO question_survey_customer_service_record (id, name, type)
                           VALUES (1, 'aspect', 'rating'),
                                  (2, 'conformity', 'rating'),
                                  (3, 'operational', 'rating'),
                                  (4, 'shipping', 'choice'),
                                  (5, 'is_link_working', 'boolean')
                           ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer_survey_customer_service_record DROP FOREIGN KEY FK_506853C5ED7607BD');
        $this->addSql('ALTER TABLE answer_survey_customer_service_record DROP FOREIGN KEY FK_506853C592CBCBBE');
        $this->addSql('ALTER TABLE answer_survey_customer_service_record DROP FOREIGN KEY FK_506853C5B03A8386');
        $this->addSql('ALTER TABLE answer_survey_customer_service_record DROP FOREIGN KEY FK_506853C5896DBBDE');
        $this->addSql('DROP TABLE answer_survey_customer_service_record');
        $this->addSql('DROP TABLE question_survey_customer_service_record');
        $this->addSql('ALTER TABLE customer_service_record ADD survey_aspect INT DEFAULT NULL, ADD survey_aspect_comment LONGTEXT DEFAULT NULL, ADD survey_conformity INT DEFAULT NULL, ADD survey_conformity_comment LONGTEXT DEFAULT NULL, ADD survey_operational INT DEFAULT NULL, ADD survey_operational_comment LONGTEXT DEFAULT NULL, ADD survey_shipping INT DEFAULT NULL, ADD survey_shipping_comment LONGTEXT DEFAULT NULL');
    }
}
