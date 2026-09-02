<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240620221031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create table CSR and Intervention + add latitude longitude on Airport (with legacy request)';
    }

    public function up(Schema $schema): void
    {
        // ALTER TABLE airport_codes ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer_service_record (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, equipment_record_id INT DEFAULT NULL, airport_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, closed_at DATETIME DEFAULT NULL, planned_at DATETIME DEFAULT NULL, description LONGTEXT NOT NULL, hourmeter INT DEFAULT NULL, status VARCHAR(50) NOT NULL, legacy_id INT NOT NULL, discr VARCHAR(255) NOT NULL, toc_legacy_id INT DEFAULT NULL, service_bulletin_lines_legacy_id INT DEFAULT NULL, service_bulletin_legacy_id INT DEFAULT NULL, INDEX IDX_3C9EAE6DB03A8386 (created_by_id), INDEX IDX_3C9EAE6D9FC03375 (equipment_record_id), INDEX IDX_3C9EAE6D289F53C8 (airport_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_service_record_file (id INT NOT NULL, customer_service_record_id INT DEFAULT NULL, INDEX IDX_99D277C417DB25B (customer_service_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, planned_by_id INT DEFAULT NULL, customer_service_record_id INT DEFAULT NULL, leader_id INT NOT NULL, started_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, planned_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, hourmeter INT DEFAULT NULL, status VARCHAR(50) NOT NULL, INDEX IDX_D11814AB24E29790 (planned_by_id), INDEX IDX_D11814AB17DB25B (customer_service_record_id), INDEX IDX_D11814AB73154ED4 (leader_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention_people (intervention_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_C2B648038EAE3863 (intervention_id), INDEX IDX_C2B648033147C936 (people_id), PRIMARY KEY(intervention_id, people_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_service_record ADD CONSTRAINT FK_3C9EAE6DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_service_record ADD CONSTRAINT FK_3C9EAE6D9FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE customer_service_record ADD CONSTRAINT FK_3C9EAE6D289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('ALTER TABLE customer_service_record_file ADD CONSTRAINT FK_99D277C417DB25B FOREIGN KEY (customer_service_record_id) REFERENCES customer_service_record (id)');
        $this->addSql('ALTER TABLE customer_service_record_file ADD CONSTRAINT FK_99D277C4BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB24E29790 FOREIGN KEY (planned_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB17DB25B FOREIGN KEY (customer_service_record_id) REFERENCES customer_service_record (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB73154ED4 FOREIGN KEY (leader_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE intervention_people ADD CONSTRAINT FK_C2B648038EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention_people ADD CONSTRAINT FK_C2B648033147C936 FOREIGN KEY (people_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE iata_codes ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_service_record DROP FOREIGN KEY FK_3C9EAE6DB03A8386');
        $this->addSql('ALTER TABLE customer_service_record DROP FOREIGN KEY FK_3C9EAE6D9FC03375');
        $this->addSql('ALTER TABLE customer_service_record DROP FOREIGN KEY FK_3C9EAE6D289F53C8');
        $this->addSql('ALTER TABLE customer_service_record_file DROP FOREIGN KEY FK_99D277C417DB25B');
        $this->addSql('ALTER TABLE customer_service_record_file DROP FOREIGN KEY FK_99D277C4BF396750');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB24E29790');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB17DB25B');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB73154ED4');
        $this->addSql('ALTER TABLE intervention_people DROP FOREIGN KEY FK_C2B648038EAE3863');
        $this->addSql('ALTER TABLE intervention_people DROP FOREIGN KEY FK_C2B648033147C936');
        $this->addSql('DROP TABLE customer_service_record');
        $this->addSql('DROP TABLE customer_service_record_file');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE intervention_people');
        $this->addSql('ALTER TABLE report_snapshot CHANGE options options JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE x_totals x_totals JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE y_totals y_totals JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE `rows` `rows` JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE metadata metadata JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE iata_codes DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE activity CHANGE metadata metadata JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', CHANGE change_set change_set JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
