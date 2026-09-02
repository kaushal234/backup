<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251008084001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC Oldest report done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE technician_on_call_oldest_report (id INT AUTO_INCREMENT NOT NULL, technician_on_call_id INT NOT NULL, sales_organisation_id INT NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', days INT NOT NULL, INDEX IDX_E5BBFCF9EC02A7D0 (technician_on_call_id), INDEX IDX_E5BBFCF9E8E5F9D1 (sales_organisation_id), UNIQUE INDEX unique_toc_oldest (technician_on_call_id, sales_organisation_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE technician_on_call_oldest_report ADD CONSTRAINT FK_E5BBFCF9EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id)');
        $this->addSql('ALTER TABLE technician_on_call_oldest_report ADD CONSTRAINT FK_E5BBFCF9E8E5F9D1 FOREIGN KEY (sales_organisation_id) REFERENCES directory_location (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call_oldest_report DROP FOREIGN KEY FK_E5BBFCF9EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_oldest_report DROP FOREIGN KEY FK_E5BBFCF9E8E5F9D1');
        $this->addSql('DROP TABLE technician_on_call_oldest_report');
    }
}
