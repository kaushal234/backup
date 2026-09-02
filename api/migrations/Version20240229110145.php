<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240229110145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Hour Meter transactions tables for ER';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE csr_hour_meter_transactions (id INT NOT NULL, customer_service_record_legacy_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE er_hour_meter_transactions (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hour_meter_transactions (id INT AUTO_INCREMENT NOT NULL, equipment_record_id INT NOT NULL, hour_meter INT NOT NULL, created_at DATETIME NOT NULL, module VARCHAR(255) NOT NULL, legacy_id INT NOT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_B09BB769FC03375 (equipment_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scm_hour_meter_transactions (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE toc_hour_meter_transactions (id INT NOT NULL, toc_legacy_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wc_hour_meter_transactions (id INT NOT NULL, warranty_claim_legacy_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE csr_hour_meter_transactions ADD CONSTRAINT FK_A4D726B8BF396750 FOREIGN KEY (id) REFERENCES hour_meter_transactions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE er_hour_meter_transactions ADD CONSTRAINT FK_29139C8CBF396750 FOREIGN KEY (id) REFERENCES hour_meter_transactions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hour_meter_transactions ADD CONSTRAINT FK_B09BB769FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
        $this->addSql('ALTER TABLE scm_hour_meter_transactions ADD CONSTRAINT FK_EABD9EE4BF396750 FOREIGN KEY (id) REFERENCES hour_meter_transactions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE toc_hour_meter_transactions ADD CONSTRAINT FK_59C11BF396750 FOREIGN KEY (id) REFERENCES hour_meter_transactions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wc_hour_meter_transactions ADD CONSTRAINT FK_6B973BFBBF396750 FOREIGN KEY (id) REFERENCES hour_meter_transactions (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE equipment_records ADD hour_meter INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE csr_hour_meter_transactions DROP FOREIGN KEY FK_A4D726B8BF396750');
        $this->addSql('ALTER TABLE er_hour_meter_transactions DROP FOREIGN KEY FK_29139C8CBF396750');
        $this->addSql('ALTER TABLE hour_meter_transactions DROP FOREIGN KEY FK_B09BB769FC03375');
        $this->addSql('ALTER TABLE toc_hour_meter_transactions DROP FOREIGN KEY FK_59C11BF396750');
        $this->addSql('ALTER TABLE wc_hour_meter_transactions DROP FOREIGN KEY FK_6B973BFBBF396750');
        $this->addSql('ALTER TABLE scm_hour_meter_transactions DROP FOREIGN KEY FK_EABD9EE4BF396750');
        $this->addSql('DROP TABLE scm_hour_meter_transactions');
        $this->addSql('DROP TABLE csr_hour_meter_transactions');
        $this->addSql('DROP TABLE er_hour_meter_transactions');
        $this->addSql('DROP TABLE hour_meter_transactions');
        $this->addSql('DROP TABLE toc_hour_meter_transactions');
        $this->addSql('DROP TABLE wc_hour_meter_transactions');

        $this->addSql('ALTER TABLE equipment_records DROP hour_meter');
    }
}
